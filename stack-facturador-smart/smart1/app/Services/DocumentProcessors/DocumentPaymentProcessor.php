<?php

namespace App\Services\DocumentProcessors;

use App\Models\Tenant\Cash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DocumentPaymentProcessor extends BaseDocumentProcessor
{
    public function process($cash_document, $status_type_id, &$methods_payment, &$result)
    {

        $document_payment = \App\Models\Tenant\DocumentPayment::find($cash_document->payment_id);
        if (!$document_payment) return null;

        $document = $document_payment->document;
        if (!in_array($document->state_type_id, $status_type_id)) return null;

        $payment_amount = $this->calculateTotal(
            $document_payment->payment,
            $document->currency_type_id,
            $document->exchange_rate_sale
        );

        // Actualizar resultados
        $result['cash_income'] += $payment_amount;
        $result['final_balance'] += $payment_amount;
 
        // Actualizar métodos de pago
        $this->updateMethodsPayment($methods_payment, $document_payment->payment_method_type_id, $payment_amount);

        // Procesar items
        foreach ($document->items as $item) {
            $result['items']++;
            $result['all_items'][] = $item;
            $result['collection_items']->push($item);
        }

        // Retornar datos del documento
        return [
            'type_transaction' => 'Venta',
            'document_type_description' => $document->document_type->description,
            'number' => $document->number_full,
            'date_of_issue' => $document_payment->date_of_payment->format('Y-m-d'),
            'date_sort' => $document->date_of_issue,
            'customer_name' => $document->customer->name,
            'customer_number' => $document->customer->number,
            'total' => $document->total,
            'currency_type_id' => $document->currency_type_id,
            'total_payments' => $payment_amount,
            'type_transaction_prefix' => 'income',
            'payment_method_description' => $this->getPaymentMethodDescription($methods_payment, $document_payment->payment_method_type_id),
            'reference' => $document_payment->reference ?? null,
        ];
    }

    public function processBatch($payment_ids, $status_type_id, &$methods_payment, &$result, $withGainItems = false)
    {
        // Cargamos todos los pagos de documentos en una sola consulta
        $document_payments = \App\Models\Tenant\DocumentPayment::with([
            'document' => function($q) use ($status_type_id) {
                $q->whereIn('state_type_id', $status_type_id)
                  ->with(['document_type', 'person','payment_method_type','tip','items']);
            }
        ])
        ->whereIn('id', $payment_ids)
        ->get();

        $processed_documents = collect();
        
        // Crear un mapa para rastrear documentos ya procesados para evitar durmplicar información
        $document_map = [];
            
        foreach ($document_payments as $document_payment) {
            $document = $document_payment->document;
            if (!$document || !in_array($document->state_type_id, $status_type_id)) continue;
            
            $payment_amount = $this->calculateTotal(
                $document_payment->payment,
                $document->currency_type_id,
                $document->exchange_rate_sale
            );
            
            // Sumar siempre el monto de este pago a los totales
            $result['cash_income'] += $payment_amount;
            $result['final_balance'] += $payment_amount;
            $result['cpe_total'] += $payment_amount;
            
            if($document_payment->payment_method_type->is_cash){
                $result['cpe_total_cash'] += $payment_amount;
            }
            
            // Actualizar métodos de pago
            $this->updateMethodsPayment($methods_payment, $document_payment->payment_method_type_id, $payment_amount);
            
            // Generar la clave única para este documento
            $document_key = 'doc_' . $document->id;
            
            // Obtener el ID del vendedor
            $seller_id = $document->seller_id ?? $document->user_id;
            
            // Solo procesamos los items y agregamos el documento a la lista una vez
            if (!isset($document_map[$document_key])) {
                $document_map[$document_key] = true;
        
                // Calcular la ganancia del documento usando el método optimizado
                $document_gain = 0;
                $document_comission = 0;
                if ($document->items && count($document->items) > 0) {
                    $total = $document->total;
                    $percentage_payment_by_cash_id = $payment_amount / $total;
                    $gain_items = $this->getGainByItems($document->items,$document_key);
                    $document_gain = $gain_items['gain'] * $percentage_payment_by_cash_id;
            
                    $document_comission = $gain_items['comission'] * $percentage_payment_by_cash_id;
                }
                
                // Procesar información del vendedor (el método processSeller ya maneja duplicados)
                // Pasamos la ganancia calculada como último parámetro
            
                $this->processSeller($result, $seller_id, $document, $payment_amount, [], $document->document_type_id, $document_gain);
                
                // Procesar items solo una vez por documento
                
                // Añadir a la colección de resultados solo una vez
                $processed_documents->push([
                    'type_transaction' => 'Venta',
                    'document_type_description' => $document->document_type->description,
                    'number' => $document->number_full,
                    'date_of_issue' => $document_payment->date_of_payment->format('Y-m-d'),
                    'date_sort' => $document->date_of_issue,
                    'customer_name' => $document->customer->name,
                    'customer_number' => $document->customer->number,
                    'total' => $document->total,
                    'currency_type_id' => $document->currency_type_id,
                    'payment_condition_id' => $document->payment_condition_id ?? '01',
                    'payment_condition_type_id' => $document->payment_condition_id ?? '01',
                    'total_payments' => $payment_amount,
                    'total_gain' => $document_gain, // Añadir la ganancia calculada
                    'total_comission' => $document_comission,
                    'type_transaction_prefix' => 'income',
                    'payment_method_description' => $this->getPaymentMethodDescription($methods_payment, $document_payment->payment_method_type_id),
                    'reference' => $document_payment->reference ?? null,
                    'document_type_id' => $document->document_type_id,
                    'total_tips' => $document->tip ? $document->tip->total : 0,
                    'seller_id' => $seller_id,
                ]);
            } else {
                // Para documentos ya procesados, actualizamos solo el monto de pago en la colección
                // Necesitamos crear una nueva colección para evitar el error de modificación indirecta
                $updated_docs = collect();
                
                foreach ($processed_documents as $existing_doc) {
                    if ($existing_doc['number'] === $document->number_full) {
                        // Crear una copia del documento con el monto de pago actualizado
                        $doc_copy = $existing_doc;
                        $doc_copy['total_payments'] += $payment_amount;
                        $updated_docs->push($doc_copy);
                    } else {
                        // Mantener el documento sin cambios
                        $updated_docs->push($existing_doc);
                    }
                }
                
                // Reemplazar la colección de documentos procesados
                $processed_documents = $updated_docs;
            }
        }

        return $processed_documents;
    }
} 
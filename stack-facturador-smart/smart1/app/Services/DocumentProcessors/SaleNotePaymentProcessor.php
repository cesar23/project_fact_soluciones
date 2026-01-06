<?php

namespace App\Services\DocumentProcessors;

use App\Models\Tenant\SaleNotePayment;
use Illuminate\Support\Facades\Log;

class SaleNotePaymentProcessor extends BaseDocumentProcessor
{
    public function process($cash_document, $status_type_id, &$methods_payment, &$result)
    {
        
        $sale_note_payment = SaleNotePayment::find($cash_document->payment_id);
        if (!$sale_note_payment) return null;

        $sale_note = $sale_note_payment->sale_note;
        if (!in_array($sale_note->state_type_id, $status_type_id)) return null;

        $payment_amount = $this->calculateTotal(
            $sale_note_payment->payment,
            $sale_note->currency_type_id,
            $sale_note->exchange_rate_sale
        );

        $result['cash_income'] += $payment_amount;
        $result['final_balance'] += $payment_amount;
        $this->updateMethodsPayment($methods_payment, $sale_note_payment->payment_method_type_id, $payment_amount);

        foreach ($sale_note->items as $item) {
            $result['items']++;
            $result['all_items'][] = $item;
            $result['collection_items']->push($item);
        }


        return [
            'type_transaction' => 'Venta',
            'document_type_description' => 'NOTA DE VENTA',
            'number' => $sale_note->number_full,
            'date_of_issue' => $sale_note->date_of_issue->format('Y-m-d'),
            'date_sort' => $sale_note->date_of_issue,
            'customer_name' => $sale_note->customer->name,
            'customer_number' => $sale_note->customer->number,
            'total' => $sale_note->total,
            'currency_type_id' => $sale_note->currency_type_id,
            'total_payments' => $payment_amount,
            'type_transaction_prefix' => 'income',
            'payment_method_description' => $this->getPaymentMethodDescription($methods_payment, $sale_note_payment->payment_method_type_id),
            'reference' => $sale_note_payment->reference ?? null,
        ];
    }

    public function processBatch($payment_ids, $status_type_id, &$methods_payment, &$result, $withGainItems = false)
    {
        // Cargamos todos los pagos de notas de venta en una sola consulta
        $sale_note_payments = SaleNotePayment::with([
            'sale_note' => function($q) use ($status_type_id) {
                $q->whereIn('state_type_id', $status_type_id)
                  ->with(['person','payment_method_type','tip','items']);
            }
        ])
        ->whereIn('id', $payment_ids)
        ->whereHas('sale_note', function ($q) {
            $q->where(function ($query) {
                $query->whereNull('quotation_id') // No tiene cotización
                      ->orWhereHas('quotation', function ($q2) {
                          $q2->doesntHave('payments'); // Tiene cotización, pero sin pagos
                      });
            });
        })
        ->get();


        $processed_documents = collect();
        
        // Crear un mapa para rastrear notas de venta ya procesadas
        $note_map = [];
            
        foreach ($sale_note_payments as $sale_note_payment) {
            $sale_note = $sale_note_payment->sale_note;
            if (!$sale_note || !in_array($sale_note->state_type_id, $status_type_id)) {
                continue;
            }

            $payment_amount = $this->calculateTotal(
                $sale_note_payment->payment,
                $sale_note->currency_type_id,
                $sale_note->exchange_rate_sale
            );
            
            // Actualizar resultados - estos siempre se suman para cada pago
            $result['cash_income'] += $payment_amount;
            $result['final_balance'] += $payment_amount;
            $result['sale_notes_total'] += $payment_amount;
            
            if($sale_note_payment->payment_method_type->is_cash){
                $result['sale_notes_total_cash'] += $payment_amount;
            }
            
            // Actualizar métodos de pago
            $this->updateMethodsPayment($methods_payment, $sale_note_payment->payment_method_type_id, $payment_amount);

            // Obtener el ID del vendedor
            $seller_id = $sale_note->seller_id ?? $sale_note->user_id;
            
            // Crear clave única para esta nota de venta
            $note_key = 'note_' . $sale_note->id;
            
            // Solo procesamos los items y agregamos la nota de venta a la lista una vez
            if (!isset($note_map[$note_key])) {
                $note_map[$note_key] = true;
                
                // Calcular la ganancia de la nota de venta usando el método optimizado
                $note_gain = 0;
                $note_comission = 0;
                if ($sale_note->items && count($sale_note->items) > 0) {
                    $total = $sale_note->total;
                    $percentage_payment_by_cash_id = $payment_amount / $total;
                    $gain_items = $this->getGainByItems($sale_note->items,$note_key);
                    $note_gain = $gain_items['gain'] * $percentage_payment_by_cash_id;
                    $note_comission = $gain_items['comission'] * $percentage_payment_by_cash_id;
                }
                
                // Procesar información del vendedor (el método processSeller ya maneja duplicados)
                // Pasamos la ganancia calculada como último parámetro
                $this->processSeller($result, $seller_id, $sale_note, $payment_amount, [], 'sale_note', $note_gain);
                
                // Procesar items solo una vez por nota
                
                // Añadir a la colección de resultados solo una vez
                $processed_documents->push([
                    'type_transaction' => 'Venta',
                    'document_type_description' => 'NOTA DE VENTA',
                    'number' => $sale_note->number_full,
                    'date_of_issue' => $sale_note->date_of_issue->format('Y-m-d'),
                    'date_sort' => $sale_note->date_of_issue,
                    'customer_name' => $sale_note->customer->name,
                    'customer_number' => $sale_note->customer->number,
                    'payment_condition_id' => $sale_note->payment_condition_id ?? '01',
                    'payment_condition_type_id' => $sale_note->payment_condition_id ?? '01',
                    'total' => $sale_note->total,
                    'currency_type_id' => $sale_note->currency_type_id,
                    'total_payments' => $payment_amount,
                    'total_gain' => $note_gain, // Añadir la ganancia calculada
                    'type_transaction_prefix' => 'income',
                    'payment_method_description' => $this->getPaymentMethodDescription($methods_payment, $sale_note_payment->payment_method_type_id),
                    'reference' => $sale_note_payment->reference ?? null,
                    'total_tips' => $sale_note->tip ? $sale_note->tip->total : 0,
                    'seller_id' => $seller_id,
                    'total_comission' => $note_comission,
                ]);
            } else {
                // Para notas ya procesadas, actualizamos solo el monto de pago en la colección
                // Necesitamos crear una nueva colección para evitar el error de modificación indirecta
                $updated_docs = collect();
                
                foreach ($processed_documents as $existing_doc) {
                    if ($existing_doc['number'] === $sale_note->number_full) {
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
<?php

namespace App\Services\DocumentProcessors;

use Modules\Sale\Models\QuotationPayment;

class QuotationPaymentProcessor extends BaseDocumentProcessor
{
    public function process($cash_document, $status_type_id, &$methods_payment, &$result)
    {
        $quotation_payment = QuotationPayment::find($cash_document->payment_id);
        if (!$quotation_payment) return null;

        $quotation = $quotation_payment->quotation;
        if (!$quotation->applyQuotationToCash() || !in_array($quotation->state_type_id, $status_type_id)) return null;

        $payment_amount = $this->calculateTotal(
            $quotation_payment->payment,
            $quotation->currency_type_id,
            $quotation->exchange_rate_sale
        );

        $result['cash_income'] += $payment_amount;
        $result['final_balance'] += $payment_amount;
        $result['quotations_total'] += $quotation->total;
        $this->updateMethodsPayment($methods_payment, $quotation_payment->payment_method_type_id, $payment_amount);

        return [
            'type_transaction' => 'Venta (Pago a cuenta)',
            'document_type_description' => 'COTIZACION',
            'number' => $quotation->number_full,
            'date_of_issue' => $quotation->date_of_issue->format('Y-m-d'),
            'date_sort' => $quotation->date_of_issue,
            'customer_name' => $quotation->customer->name,
            'customer_number' => $quotation->customer->number,
            'total' => $quotation->total,
            'currency_type_id' => $quotation->currency_type_id,
            'total_payments' => $payment_amount,
            'type_transaction_prefix' => 'income',
            'seller_id' => $quotation->seller_id,
            'seller_name' => $quotation->seller->name,
            'payment_method_description' => $this->getPaymentMethodDescription($methods_payment, $quotation_payment->payment_method_type_id),
            'reference' => $quotation_payment->reference ?? null,
        ];
    }

    public function processBatch($payment_ids, $status_type_id, &$methods_payment, &$result,$withGainItems = false)
    {
        // Cargamos todos los pagos de cotizaciones en una sola consulta
        $quotation_payments = \Modules\Sale\Models\QuotationPayment::with([
            'quotation' => function($q) use ($status_type_id) {
                $q->whereIn('state_type_id', $status_type_id)
                  ->with(['person', 'seller']);
            }
        ])
        ->whereIn('id', $payment_ids)
        ->get();

        $processed_documents = collect();

        foreach ($quotation_payments as $quotation_payment) {
            $quotation = $quotation_payment->quotation;
            if (!$quotation || !$quotation->applyQuotationToCash() || !in_array($quotation->state_type_id, $status_type_id)) continue;

            $payment_amount = $this->calculateTotal(
                $quotation_payment->payment,
                $quotation->currency_type_id,
                $quotation->exchange_rate_sale
            );

            // Actualizar resultados
            $result['cash_income'] += $payment_amount;
            $result['final_balance'] += $payment_amount;
            $result['quotations_total'] += $quotation->total;

        
            
            // Actualizar métodos de pago
            $this->updateMethodsPayment($methods_payment, $quotation_payment->payment_method_type_id, $payment_amount);

            $seller_id = $quotation->seller_id ?? $quotation->user_id;

            $quotation_key = 'quotation_' . $quotation->id;
            if (!isset($note_map[$quotation_key])) {
                $note_map[$quotation_key] = true;
                
                // Calcular la ganancia de la nota de venta usando el método optimizado
                $quotation_gain = 0;
                $quotation_comission = 0;
                if ($quotation->items && count($quotation->items) > 0) {
                    
                    $total = $quotation->total;
                    $percentage_payment_by_cash_id = $payment_amount / $total;
                    $gain_items = $this->getGainByItems($quotation->items,$quotation_key);
                    $quotation_gain = $gain_items['gain'] * $percentage_payment_by_cash_id;
                    $quotation_comission = $gain_items['comission'] * $percentage_payment_by_cash_id;
                }
                
                // Procesar información del vendedor (el método processSeller ya maneja duplicados)
                // Pasamos la ganancia calculada como último parámetro
                $this->processSeller($result, $seller_id, $quotation, $payment_amount, [], 'quotation', $quotation_gain);
                
                // Procesar items solo una vez por nota
                
                // Añadir a la colección de resultados solo una vez
                $processed_documents->push([
                    'type_transaction' => 'Venta',
                    'document_type_description' => 'COTIZACION',
                    'number' => $quotation->number_full,
                    'date_of_issue' => $quotation->date_of_issue->format('Y-m-d'),
                    'date_sort' => $quotation->date_of_issue,
                    'customer_name' => $quotation->customer->name,
                    'customer_number' => $quotation->customer->number,
                    'payment_condition_id' => $quotation->payment_condition_id ?? '01',
                    'payment_condition_type_id' => $quotation->payment_condition_id ?? '01',
                    'total' => $quotation->total,
                    'currency_type_id' => $quotation->currency_type_id,
                    'total_payments' => $payment_amount,
                    'total_gain' => $quotation_gain, // Añadir la ganancia calculada
                    'type_transaction_prefix' => 'income',
                    'payment_method_description' => $this->getPaymentMethodDescription($methods_payment, $quotation_payment->payment_method_type_id),
                    'reference' => $quotation_payment->reference ?? null,
                    'total_tips' => $quotation->tip ? $quotation->tip->total : 0,
                    'seller_id' => $seller_id,
                    'total_comission' => $quotation_comission,
                ]);
            } else {
                // Para notas ya procesadas, actualizamos solo el monto de pago en la colección
                // Necesitamos crear una nueva colección para evitar el error de modificación indirecta
                $updated_docs = collect();
                
                foreach ($processed_documents as $existing_doc) {
                    if ($existing_doc['number'] === $quotation->number_full) {  
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
            // if (isset($processed_documents[$quotation_key])) {
            // Añadir a la colección de resultados
            // $processed_documents->push([
            //     'type_transaction' => 'Venta (Pago a cuenta)',
            //     'document_type_description' => 'COTIZACION',
            //     'number' => $quotation->number_full,
            //     'date_of_issue' => $quotation->date_of_issue->format('Y-m-d'),
            //     'date_sort' => $quotation->date_of_issue,
            //     'customer_name' => $quotation->customer->name,
            //     'customer_number' => $quotation->customer->number,
            //     'total' => $quotation->total,
            //     'currency_type_id' => $quotation->currency_type_id,
            //     'total_payments' => $payment_amount,
            //     'type_transaction_prefix' => 'income',
            //     'seller_id' => $quotation->seller_id,
            //     'seller_name' => $quotation->seller->name ?? 'N/A',
            //     'payment_method_description' => $this->getPaymentMethodDescription($methods_payment, $quotation_payment->payment_method_type_id),
            //     'reference' => $quotation_payment->reference ?? null,
            // ]);
        }

        return $processed_documents;
    }
} 
<?php

namespace App\Services\DocumentProcessors;

use Modules\Sale\Models\TechnicalServicePayment;

class TechnicalServicePaymentProcessor extends BaseDocumentProcessor
{
    public function process($cash_document, $status_type_id, &$methods_payment, &$result)
    {
        $technical_service_payment = TechnicalServicePayment::find($cash_document->payment_id);
        if (!$technical_service_payment) return null;

        $technical_service = $technical_service_payment->technical_service;
        if (!$technical_service->applyToCash()) return null;

        $payment_amount = $technical_service_payment->payment;

        $result['cash_income'] += $payment_amount;
        $result['final_balance'] += $payment_amount;

        $this->updateMethodsPayment($methods_payment, $technical_service_payment->payment_method_type_id, $payment_amount);

        return [
            'type_transaction' => 'Venta',
            'document_type_description' => 'Servicio técnico',
            'number' => 'TS-' . $technical_service->id,
            'date_of_issue' => $technical_service->date_of_issue->format('Y-m-d'),
            'date_sort' => $technical_service->date_of_issue,
            'customer_name' => $technical_service->customer->name,
            'customer_number' => $technical_service->customer->number,
            'total' => $technical_service->total_record,
            'currency_type_id' => 'PEN',
            'total_payments' => $payment_amount,
            'type_transaction_prefix' => 'income',
            'payment_method_description' => $this->getPaymentMethodDescription($methods_payment, $technical_service_payment->payment_method_type_id),
            'reference' => $technical_service_payment->reference ?? null,
        ];
    }

    public function processBatch($payment_ids, $status_type_id, &$methods_payment, &$result,$withGainItems = false)
    {
        // Cargamos todos los pagos de servicios técnicos en una sola consulta
        $technical_service_payments = \Modules\Sale\Models\TechnicalServicePayment::with([
            'technical_service' => function($q) {
                $q->with(['person']);
            }
        ])
        ->whereIn('id', $payment_ids)
        ->get();

        $processed_documents = collect();

        foreach ($technical_service_payments as $technical_service_payment) {
            $technical_service = $technical_service_payment->technical_service;
            if (!$technical_service) continue;

            $payment_amount = $technical_service_payment->payment;

            // Actualizar resultados
            $result['cash_income'] += $payment_amount;
            $result['final_balance'] += $payment_amount;
            
            // Actualizar métodos de pago
            $this->updateMethodsPayment($methods_payment, $technical_service_payment->payment_method_type_id, $payment_amount);

            // Añadir a la colección de resultados
            $processed_documents->push([
                'type_transaction' => 'Servicio Técnico',
                'document_type_description' => 'SERVICIO TÉCNICO',
                'number' => $technical_service->identifier,
                'date_of_issue' => $technical_service_payment->date_of_payment->format('Y-m-d'),
                'date_sort' => $technical_service->date_of_issue,
                'customer_name' => $technical_service->customer->name ?? 'N/A',
                'customer_number' => $technical_service->customer->number ?? 'N/A',
                'total' => $technical_service->cost,
                'currency_type_id' => 'PEN',
                'total_payments' => $payment_amount,
                'type_transaction_prefix' => 'income',
                'payment_method_description' => $this->getPaymentMethodDescription($methods_payment, $technical_service_payment->payment_method_type_id),
                'reference' => $technical_service_payment->reference ?? null,
            ]);
        }

        return $processed_documents;
    }
} 
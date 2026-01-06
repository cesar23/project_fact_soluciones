<?php

namespace App\Services\DocumentProcessors;

use Carbon\Carbon;
use Modules\Pos\Models\CashTransaction;

class CashTransactionProcessor extends BaseDocumentProcessor
{
    public function process($cash_document, $status_type_id, &$methods_payment, &$result)
    {
        $cash_transaction = CashTransaction::find($cash_document->payment_id);
        if (!$cash_transaction) return null;

        $payment_amount = $cash_transaction->payment;

        // if ($cash_transaction->type === 'income') {
        //     $result['cash_income'] += $payment_amount;
        //     $result['final_balance'] += $payment_amount;
        // } else {
        //     $result['cash_egress'] += $payment_amount;
        //     $result['final_balance'] -= $payment_amount;
        // }

        $this->updateMethodsPayment($methods_payment, $cash_transaction->payment_method_type_id, $payment_amount);
        $date = $cash_transaction->date;
        if ($date instanceof Carbon) {
            $date = $date->format('Y-m-d');
        }
        return [
            'type_transaction' => ucfirst($cash_transaction->type),
            'document_type_description' => 'TRANSACCIÓN DE CAJA',
            'number' => $cash_transaction->reference_number ?? '-',
            'date_of_issue' => $date,
            'date_sort' => $cash_transaction->date,
            'customer_name' => $cash_transaction->description ?? '-',
            'customer_number' => '-',
            'total' => $payment_amount,
            'currency_type_id' => 'PEN',
            'total_payments' => $payment_amount,
            'type_transaction_prefix' => $cash_transaction->type,
            'payment_method_description' => $this->getPaymentMethodDescription($methods_payment, $cash_transaction->payment_method_type_id),
            'reference' => $cash_transaction->reference_number ?? null,
        ];
    }

    public function processBatch($payment_ids, $status_type_id, &$methods_payment, &$result,$withGainItems = false)
    {
        // Cargamos todas las transacciones de caja en una sola consulta
        $cash_transactions = CashTransaction::whereIn('id', $payment_ids)->get();

        $processed_documents = collect();

        foreach ($cash_transactions as $cash_transaction) {
            $payment_amount = $cash_transaction->payment;

            // Actualizar resultados según el tipo de transacción
            // if ($cash_transaction->type === 'income') {
            //     $result['cash_income'] += $payment_amount;
            //     $result['final_balance'] += $payment_amount;
            // } else {
            //     $result['cash_egress'] += $payment_amount;
            //     $result['final_balance'] -= $payment_amount;
            // }
            
            // Actualizar métodos de pago
            $this->updateMethodsPayment($methods_payment, $cash_transaction->payment_method_type_id, $payment_amount);

            // Añadir a la colección de resultados
            $processed_documents->push([
                'type_transaction' => ucfirst($cash_transaction->type),
                'document_type_description' => 'TRANSACCIÓN DE CAJA',
                'number' => $cash_transaction->reference_number ?? '-',
                'date_of_issue' => $cash_transaction->date->format('Y-m-d'),
                'date_sort' => $cash_transaction->date,
                'customer_name' => $cash_transaction->description ?? '-',
                'customer_number' => '-',
                'total' => $payment_amount,
                'currency_type_id' => 'PEN',
                'total_payments' => $payment_amount,
                'type_transaction_prefix' => $cash_transaction->type,
                'payment_method_description' => $this->getPaymentMethodDescription($methods_payment, $cash_transaction->payment_method_type_id),
                'reference' => $cash_transaction->reference_number ?? null,
            ]);
        }

        return $processed_documents;
    }
} 
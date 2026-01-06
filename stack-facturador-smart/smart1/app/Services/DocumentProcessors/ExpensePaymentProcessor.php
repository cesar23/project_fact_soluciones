<?php

namespace App\Services\DocumentProcessors;

use Modules\Expense\Models\ExpensePayment;

class ExpensePaymentProcessor extends BaseDocumentProcessor
{
    public function process($cash_document, $status_type_id, &$methods_payment, &$result)
    {
        $expense_payment = ExpensePayment::find($cash_document->payment_id);
        if (!$expense_payment) return null;

        $expense = $expense_payment->expense;
        if ($expense->state_type_id !== '05') return null;

        $payment_amount = $this->calculateTotal(
            $expense_payment->payment,
            $expense->currency_type_id,
            $expense->exchange_rate_sale
        );

        $result['cash_egress'] += $payment_amount;
        $result['final_balance'] -= $payment_amount;

        if ($expense_payment->expense_method_type_id == "1") {
            $this->updateMethodsPayment($methods_payment, $expense_payment->expense_method_type_id, -$payment_amount);
        }

        return [
            'type_transaction' => 'Gasto',
            'document_type_description' => $expense->expense_type->description,
            'number' => $expense->number,
            'date_of_issue' => $expense->date_of_issue->format('Y-m-d'),
            'date_sort' => $expense->date_of_issue,
            'customer_name' => $expense->supplier->name,
            'customer_number' => $expense->supplier->number,
            'total' => -$payment_amount,
            'currency_type_id' => $expense->currency_type_id,
            'total_payments' => $payment_amount,
            'type_transaction_prefix' => 'egress',
            'reference' => $expense_payment->reference ?? null,
        ];
    }

    public function processBatch($payment_ids, $status_type_id, &$methods_payment, &$result,$withGainItems = false)
    {
        // Cargamos todos los pagos de gastos en una sola consulta
        $expense_payments = \Modules\Expense\Models\ExpensePayment::with([
            'expense' => function($q) use ($status_type_id) {
                $q->whereIn('state_type_id', $status_type_id)
                  ->with(['expense_type', 'supplier']);
            }
        ])
        ->whereIn('id', $payment_ids)
        ->get();

        $processed_documents = collect();

        foreach ($expense_payments as $expense_payment) {
            $expense = $expense_payment->expense;
            if (!$expense || !in_array($expense->state_type_id, $status_type_id)) continue;

            $payment_amount = $this->calculateTotal(
                $expense_payment->payment,
                $expense->currency_type_id,
                $expense->exchange_rate_sale ?? 1
            );
            // Actualizar resultados
            $result['cash_egress'] += $payment_amount;
            $result['final_balance'] -= $payment_amount;
            
            // Actualizar métodos de pago
            $this->updateMethodsPayment($methods_payment, $expense_payment->payment_method_type_id, $payment_amount);

            // Añadir a la colección de resultados
            $processed_documents->push([
                'type_transaction' => 'Gasto',
                'document_type_description' => $expense->expense_type->description ?? 'GASTO',
                'number' => $expense->number,
                'date_of_issue' => $expense_payment->date_of_payment->format('Y-m-d'),
                'date_sort' => $expense->date_of_issue,
                'customer_name' => $expense->supplier->name ?? 'N/A',
                'customer_number' => $expense->supplier->number ?? 'N/A',
                'total' => $expense->total,
                'currency_type_id' => $expense->currency_type_id,
                'total_payments' => $payment_amount,
                'type_transaction_prefix' => 'expense',
                'payment_method_description' => $this->getPaymentMethodDescription($methods_payment, $expense_payment->payment_method_type_id),
                'reference' => $expense_payment->reference ?? null,
            ]);
        }

        return $processed_documents;
    }
} 
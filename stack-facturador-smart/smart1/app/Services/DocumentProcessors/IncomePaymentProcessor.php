<?php

namespace App\Services\DocumentProcessors;

use Modules\Finance\Models\IncomePayment;

class IncomePaymentProcessor extends BaseDocumentProcessor
{
    public function process($cash_document, $status_type_id, &$methods_payment, &$result)
    {
        $income_payment = IncomePayment::find($cash_document->payment_id);
        if (!$income_payment) return null;

        $income = $income_payment->income;
        if ($income->state_type_id !== '05') return null;

        $payment_amount = $this->calculateTotal(
            $income_payment->payment,
            $income->currency_type_id,
            $income->exchange_rate_sale
        );

        $result['cash_income'] += $payment_amount;
        $result['final_balance'] += $payment_amount;

        $this->updateMethodsPayment($methods_payment, $income_payment->payment_method_type_id, $payment_amount);

        return [
            'type_transaction' => 'Ingreso',
            'document_type_description' => $income->income_type->description,
            'number' => $income->id,
            'date_of_issue' => $income->date_of_issue->format('Y-m-d'),
            'date_sort' => $income->date_of_issue,
            'customer_name' => $income->customer,
            'customer_number' => '-',
            'total' => $payment_amount,
            'currency_type_id' => $income->currency_type_id,
            'total_payments' => $payment_amount,
            'type_transaction_prefix' => 'income',
            'payment_method_description' => $this->getPaymentMethodDescription($methods_payment, $income_payment->payment_method_type_id),
            'reference' => $income_payment->reference ?? null,
        ];
    }
} 
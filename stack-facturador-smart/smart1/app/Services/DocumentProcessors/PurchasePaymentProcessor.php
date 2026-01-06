<?php

namespace App\Services\DocumentProcessors;

use App\Models\Tenant\PurchasePayment;

class PurchasePaymentProcessor extends BaseDocumentProcessor
{
    public function process($cash_document, $status_type_id, &$methods_payment, &$result)
    {
        $purchase_payment = PurchasePayment::find($cash_document->payment_id);
        if (!$purchase_payment) return null;

        $purchase = $purchase_payment->purchase;
        if (!in_array($purchase->state_type_id, $status_type_id)) return null;

        $payment_amount = $this->calculateTotal(
            $purchase_payment->payment,
            $purchase->currency_type_id,
            $purchase->exchange_rate_sale
        );

        $result['cash_egress'] += $payment_amount;
        $result['final_balance'] -= $payment_amount;

        $this->updateMethodsPayment($methods_payment, $purchase_payment->payment_method_type_id, -$payment_amount);

        return [
            'type_transaction' => 'Compra',
            'document_type_description' => $purchase->document_type->description,
            'number' => $purchase->number_full,
            'date_of_issue' => $purchase->date_of_issue->format('Y-m-d'),
            'date_sort' => $purchase->date_of_issue,
            'customer_name' => $purchase->supplier->name,
            'customer_number' => $purchase->supplier->number,
            'total' => $purchase->total,
            'currency_type_id' => $purchase->currency_type_id,
            'total_payments' => $payment_amount,
            'type_transaction_prefix' => 'egress',
            'payment_method_description' => $this->getPaymentMethodDescription($methods_payment, $purchase_payment->payment_method_type_id),
            'reference' => $purchase_payment->reference ?? null,
        ];
    }

    public function processBatch($payment_ids, $status_type_id, &$methods_payment, &$result,$withGainItems = false){
        $purchase_payments = PurchasePayment::whereIn('id', $payment_ids)->get();

        foreach ($purchase_payments as $purchase_payment) {
            $purchase = $purchase_payment->purchase;
            if (!in_array($purchase->state_type_id, $status_type_id)) continue;
        }
    }
} 
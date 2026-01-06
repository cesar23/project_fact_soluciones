<?php

namespace App\Services\DocumentProcessors;

use App\Models\Tenant\PackageHandlerPayment;

class PackageHandlerPaymentProcessor extends BaseDocumentProcessor
{
    public function process($cash_document, $status_type_id, &$methods_payment, &$result)
    {
        $package_handler_payment = PackageHandlerPayment::find($cash_document->payment_id);
        if (!$package_handler_payment) return null;

        $package_handler = $package_handler_payment->package_handler;
        
        $payment_amount = $this->calculateTotal(
            $package_handler_payment->payment,
            $package_handler->currency_type_id,
            $package_handler->exchange_rate_sale
        );

        $result['cash_income'] += $payment_amount;
        $result['final_balance'] += $payment_amount;

        $this->updateMethodsPayment($methods_payment, $package_handler_payment->payment_method_type_id, $payment_amount);

        foreach ($package_handler->items as $item) {
            $result['items']++;
            $result['all_items'][] = $item;
            $result['collection_items']->push($item);
        }

        return [
            'type_transaction' => 'Venta',
            'document_type_description' => 'TICKET DE ENCOMIENDA',
            'number' => $package_handler->series . "-" . $package_handler->number,
            'date_of_issue' => $package_handler->date_of_issue->format('Y-m-d'),
            'date_sort' => $package_handler->date_of_issue,
            'customer_name' => $package_handler->sender->name,
            'customer_number' => $package_handler->sender->number,
            'total' => $package_handler->total,
            'currency_type_id' => $package_handler->currency_type_id,
            'total_payments' => $payment_amount,
            'type_transaction_prefix' => 'income',
            'payment_method_description' => $this->getPaymentMethodDescription($methods_payment, $package_handler_payment->payment_method_type_id),
            'reference' => $package_handler_payment->reference ?? null,
        ];
    }
} 
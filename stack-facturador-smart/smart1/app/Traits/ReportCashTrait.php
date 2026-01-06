<?php

namespace App\Traits;

trait ReportCashTrait
{
    private function getTotalCashPaymentMethodType01($data)
    {
        return $data['total_payment_cash_01_document'] + 
               $data['total_payment_cash_01_sale_note'];
    }

    private function sumMethodsPayment($data, $type)
    {
        return collect($data['methods_payment'])
            ->where($type, true)
            ->sum('sum');
    }
} 
<?php

namespace Modules\Purchase\Traits;

use App\Models\Tenant\{
    Advance,
    Purchase,
    Cash
};

trait PurchaseTrait
{

    public function createCashDocument()
    {

        Purchase::created(function ($purchase) {
            // $payments = $purchase->payments;
            // $payWithAdvance = false;
            // if ($payments == 1) {
            //     $payment = $payments->first();
            //     $global_payment = $payment->global_payment;
            //     if ($global_payment) {
            //         $destination_type = $global_payment->destination_type;
            //         if ($destination_type == Advance::class) {

            //             $payWithAdvance = true;
            //         }
            //     }
            // }
            // if ($payWithAdvance) {
            //     $destination_id = $global_payment->destination_id;
            //     $advance = Advance::find($destination_id);
            //     $advance->advance_documents()->create(['purchase_id' => $purchase->id]);
            //     $advance->discount($purchase->total);
            // } else {
            //     $cash = Cash::whereActive()->first();
            //     if ($cash) {
            //         $cash->cash_documents()->create(['purchase_id' => $purchase->id]);
            //     }
            // }
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_09_17_2024330_purchase_payment_fee_update
class PurchasePaymentFeeUpdate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tenant_connection = DB::connection('tenant');
            $purchases = $tenant_connection->table('purchases')
            ->leftJoin('purchase_payments', 'purchases.id', '=', 'purchase_payments.purchase_id')
            ->where('date_of_issue', '>=', '2025-01-01')
            ->where('payment_condition_id', '02')
            ->groupBy('purchases.id', 'purchases.date_of_issue', 'purchases.total')
            ->havingRaw('IFNULL(SUM(purchase_payments.payment), 0) <= purchases.total AND EXISTS (SELECT 1 FROM purchase_fee pf WHERE pf.purchase_id = purchases.id AND pf.is_canceled = 0)')
            ->select('purchases.id', 'purchases.date_of_issue', 'purchases.total')
            ->get();
        
            foreach ($purchases as $purchase) {
                $purchase_id = $purchase->id;
                (new \Modules\Purchase\Http\Controllers\PurchasePaymentController())->updatePurchaseFees($purchase_id);
            }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

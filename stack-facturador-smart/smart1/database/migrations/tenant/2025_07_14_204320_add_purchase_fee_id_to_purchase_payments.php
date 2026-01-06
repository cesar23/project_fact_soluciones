<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_07_14_204320_add_purchase_fee_id_to_purchase_payments
class AddPurchaseFeeIdToPurchasePayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
            Schema::table('purchase_payments', function (Blueprint $table) {
                $table->unsignedInteger('purchase_fee_id')->nullable();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_payments', function (Blueprint $table) {
            $table->dropColumn('purchase_fee_id');
        });
    }
}

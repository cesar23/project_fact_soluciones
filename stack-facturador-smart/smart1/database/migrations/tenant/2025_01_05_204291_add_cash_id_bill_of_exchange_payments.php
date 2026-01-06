<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//2025_01_05_204291_add_cash_id_bill_of_exchange_payments
class AddCashIdBillOfExchangePayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::table('bills_of_exchange_payments', function (Blueprint $table) {
            $table->unsignedInteger('cash_id')->nullable();
        });
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('bills_of_exchange_payments', function (Blueprint $table) {
            $table->dropColumn('cash_id');
        });
      
    }
}

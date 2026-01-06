<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrencyTypeIdExchangeRateSaleBillExchangePay extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bills_of_exchange_pay',function (Blueprint $table){
            $table->string('currency_type_id')->default('PEN');
            $table->decimal('exchange_rate_sale', 12, 2)->default(1);
            $table->foreign('currency_type_id')->references('id')->on('cat_currency_types');

        });

    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bills_of_exchange_pay',function (Blueprint $table){
            $table->dropColumn('currency_type_id');
            $table->dropColumn('exchange_rate_sale');
        });
        
    }
}

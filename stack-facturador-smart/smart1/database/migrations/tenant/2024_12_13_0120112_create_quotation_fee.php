<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2024_12_13_0120112_create_quotation_fee
class CreateQuotationFee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('quotation_fee')) {
            Schema::create('quotation_fee', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('quotation_id');
                $table->date('date');
                $table->string('currency_type_id');
                $table->char('payment_method_type_id', 2)->nullable();
                $table->decimal('amount', 12, 2);
                $table->foreign('quotation_id')->references('id')->on('quotations');
                $table->foreign('currency_type_id')->references('id')->on('cat_currency_types');
                $table->foreign('payment_method_type_id')->references('id')->on('payment_method_types');
            });
        }
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quotation_fee');
    }
}

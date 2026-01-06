<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillOfExchangePay extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::create('bills_of_exchange_pay', function (Blueprint $table) {
            $table->increments('id');
            $table->string("series", 15);
            $table->string("number", 15);
            $table->date("date_of_due");
            $table->decimal('total', 12, 2);
            $table->boolean("total_canceled")->default(false);
            $table->unsignedInteger('establishment_id');
            $table->unsignedInteger('supplier_id');
            $table->unsignedInteger('user_id');
            $table->timestamps();
            $table->foreign('establishment_id')->references('id')->on('establishments')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('persons')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
        
        
        Schema::create('bills_of_exchange_documents_pay', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('bill_of_exchange_id');
            $table->unsignedInteger('purchase_id');
            $table->decimal('total', 12, 2);
            $table->foreign('bill_of_exchange_id')->references('id')->on('bills_of_exchange_pay')->onDelete('cascade');
            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('cascade');
        });
        Schema::create('bills_of_exchange_payments_pay', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('bill_of_exchange_id');
            $table->date('date_of_payment');
            $table->char('payment_method_type_id', 2);
            $table->boolean('has_card')->default(false);
            $table->char('card_brand_id', 2)->nullable();
            $table->string('reference')->nullable();
            $table->decimal('payment', 12, 2);
            $table->boolean('total_canceled')->default(false);
        });
        Schema::table('cash_documents', function (Blueprint $table) {
            $table->unsignedInteger('bill_of_exchange_pay_id')->nullable();
            $table->foreign('bill_of_exchange_pay_id')->references('id')->on('bills_of_exchange_pay')->onDelete('cascade');
        });
        //bill_of_exchange_id
        Schema::table('purchases', function (Blueprint $table) {
            $table->unsignedInteger('bill_of_exchange_pay_id')->nullable();
            $table->foreign('bill_of_exchange_pay_id')->references('id')->on('bills_of_exchange_pay')->onDelete('cascade');
            
        });
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['bill_of_exchange_pay_id']);
            $table->dropColumn('bill_of_exchange_pay_id');
        });
        Schema::table('cash_documents', function (Blueprint $table) {
            $table->dropForeign(['bill_of_exchange_pay_id']);
            $table->dropColumn('bill_of_exchange_pay_id');
        });
        Schema::dropIfExists('bills_of_exchange_documents_pay');
        Schema::dropIfExists('bills_of_exchange_payments_pay');
        Schema::dropIfExists('bills_of_exchange_pay');
        
        
    }
}

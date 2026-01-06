<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillOfExchange extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::create('bills_of_exchange', function (Blueprint $table) {
            $table->increments('id');
            $table->string("series", 15);
            $table->string("number", 15);
            $table->date("date_of_due");
            $table->decimal('total', 12, 2);
            $table->unsignedInteger('establishment_id');
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('user_id');
            $table->timestamps();
        });
        
        
        Schema::create('bills_of_exchange_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('bill_of_exchange_id');
            $table->unsignedInteger('document_id');
            $table->decimal('total', 12, 2);
        });
        Schema::create('bills_of_exchange_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('bill_of_exchange_id');
            $table->date('date_of_payment');
            $table->char('payment_method_type_id', 2);
            $table->boolean('has_card')->default(false);
            $table->char('card_brand_id', 2)->nullable();
            $table->string('reference')->nullable();
            $table->decimal('payment', 12, 2);
        });
        Schema::table('cash_documents', function (Blueprint $table) {
            $table->unsignedInteger('bill_of_exchange_id')->nullable();
        });
        //bill_of_exchange_id
        Schema::table('documents', function (Blueprint $table) {
            $table->unsignedInteger('bill_of_exchange_id')->nullable();
        });
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::dropIfExists('bills_of_exchange');
        Schema::dropIfExists('bills_of_exchange_documents');
        Schema::dropIfExists('bills_of_exchange_payments');
        Schema::table('cash_documents', function (Blueprint $table) {
            $table->dropColumn('bill_of_exchange_id');
        });
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('bill_of_exchange_id');
        });
    }
}

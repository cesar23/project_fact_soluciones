<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvanceDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advances_documents', function (Blueprint $table) {
            $table->increments('id'); 
            $table->unsignedInteger('advance_id');
            $table->unsignedInteger('document_id')->nullable(); 
            $table->unsignedInteger('sale_note_id')->nullable();
            $table->unsignedInteger('purchase_id')->nullable();
            $table->unsignedInteger('expense_payment_id')->nullable();
            $table->unsignedInteger('technical_service_id')->nullable();
            $table->unsignedInteger('quotation_id')->nullable();
            $table->unsignedInteger('income_payment_id')->nullable();
            $table->unsignedInteger('package_handler_id')->nullable();
            $table->unsignedInteger('bill_of_exchange_id')->nullable();
            $table->unsignedInteger('bill_of_exchange_pay_id')->nullable();


            $table->foreign('income_payment_id')->references('id')->on('income_payments')->onDelete('cascade');
            $table->foreign('quotation_id')->references('id')->on('quotations');
            $table->foreign('technical_service_id')->references('id')->on('technical_services')->onDelete('cascade');
            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('cascade');
            $table->foreign('expense_payment_id')->references('id')->on('expense_payments')->onDelete('cascade');
            $table->foreign('sale_note_id')->references('id')->on('sale_notes')->onDelete('cascade');
            $table->foreign('advance_id')->references('id')->on('advances')->onDelete('cascade'); 
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade'); 

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advances_documents');
    }
}

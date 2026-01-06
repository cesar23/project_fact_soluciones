<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

//2025_07_16_153356_create_payments_with_credit_note
class CreatePaymentsWithCreditNote extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
        Schema::create('payments_with_credit_note', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('sale_note_payment_id')->nullable();
            $table->unsignedInteger('document_payment_id')->nullable();
            $table->unsignedInteger('expense_payment_id')->nullable();
            $table->unsignedInteger('note_id');
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();
            $table->foreign('sale_note_payment_id')->references('id')->on('sale_note_payments');
            $table->foreign('document_payment_id')->references('id')->on('document_payments');
            $table->foreign('expense_payment_id')->references('id')->on('expense_payments');
            $table->foreign('note_id')->references('id')->on('notes');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments_with_credit_note');
    }
}

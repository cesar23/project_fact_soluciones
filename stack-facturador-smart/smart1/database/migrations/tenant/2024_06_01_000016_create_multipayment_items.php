<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMultipaymentItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('multipayment_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('multipayment_id')->nullable();
            $table->unsignedInteger('sale_note_payment_id')->nullable();
            $table->unsignedInteger('document_payment_id')->nullable();
            $table->decimal('total', 12, 2);
            $table->decimal('remaining', 12, 2);
            $table->foreign('sale_note_payment_id')->references('id')->on('sale_note_payments');
            $table->foreign('document_payment_id')->references('id')->on('document_payments');
            $table->foreign('multipayment_id')->references('id')->on('multipayments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        Schema::dropIfExists('multipayment_items');
        
        
    }
}

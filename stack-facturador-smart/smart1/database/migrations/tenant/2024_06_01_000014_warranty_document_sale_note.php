<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WarrantyDocumentSaleNote extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warranty_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sale_note_id')->nullable();
            $table->unsignedInteger('document_id')->nullable();
            $table->unsignedInteger('expense_id')->nullable();
            $table->decimal('quantity', 12, 2);
            $table->decimal('total', 12, 2);
            $table->string('comment');
            $table->boolean('state')->default(1);
            $table->foreign('sale_note_id')->references('id')->on('sale_notes');
            $table->foreign('document_id')->references('id')->on('documents');
            $table->foreign('expense_id')->references('id')->on('expenses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        Schema::dropIfExists('warranty_documents');
        
        
    }
}

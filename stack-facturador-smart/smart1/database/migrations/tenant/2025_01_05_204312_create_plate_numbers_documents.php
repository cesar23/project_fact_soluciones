<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//2025_01_05_204312_create_plate_numbers_documents

class CreatePlateNumbersDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::create('plate_numbers_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plate_number_id');
            $table->unsignedInteger('document_id')->nullable();
            $table->unsignedInteger('sale_note_id')->nullable();
            $table->unsignedInteger('quotation_id')->nullable();
            $table->foreign('plate_number_id')->references('id')->on('plate_numbers');
            $table->foreign('document_id')->references('id')->on('documents');
            $table->foreign('sale_note_id')->references('id')->on('sale_notes');
            $table->foreign('quotation_id')->references('id')->on('quotations');
            $table->timestamps();
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
        Schema::dropIfExists('plate_numbers_documents');
    }
}

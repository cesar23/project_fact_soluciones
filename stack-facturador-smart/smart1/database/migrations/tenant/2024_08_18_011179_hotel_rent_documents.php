<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 2024_08_18_011179_hotel_rent_documents



class HotelRentDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('hotel_rent_documents')) {
            Schema::create('hotel_rent_documents', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('rent_id');
                $table->unsignedInteger('sale_note_id')->nullable();
                $table->unsignedInteger('document_id')->nullable();
                $table->boolean('is_advance')->default(false);
                $table->timestamps();
                $table->foreign('rent_id')->references('id')->on('hotel_rents')->onDelete('cascade');
                $table->foreign('sale_note_id')->references('id')->on('sale_notes')->onDelete('cascade');
                $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
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
        Schema::dropIfExists('hotel_rent_documents');
    }
}

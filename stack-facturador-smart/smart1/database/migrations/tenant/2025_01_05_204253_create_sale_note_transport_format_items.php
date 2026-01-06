<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204253_create_sale_note_transport_format_items

class CreateSaleNoteTransportFormatItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transport_format_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_format_id')->constrained('transport_format');
            $table->unsignedInteger('sale_note_id');
            
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
        Schema::dropIfExists('transport_format_items');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTransportDispatchTableSaleNotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_note_transport_dispatches',function (Blueprint $table){
            $table->increments('id');
            $table->unsignedInteger('sale_note_id');
            $table->string('s_document_id');
            $table->string('sender_number_identity_document');
            $table->string('sender_passenger_fullname');
            $table->string('sender_telephone');
            $table->string('r_document_id');
            $table->string('recipient_number_identity_document');
            $table->string('recipient_passenger_fullname');
            $table->string('recipient_telephone');
            $table->foreign('sale_note_id')->references('id')->on('sale_notes')->onDelete('cascade');;
            $table->foreign('s_document_id')->references('id')->on('cat_identity_document_types');
            $table->foreign('r_document_id')->references('id')->on('cat_identity_document_types');
        });
     
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sale_note_transport_dispatches');
    }
}

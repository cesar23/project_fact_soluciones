<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTransportDispatchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_transport_dispatches',function (Blueprint $table){
            $table->increments('id');
            $table->unsignedInteger('document_id');
            $table->string('s_document_id');
            $table->string('sender_number_identity_document');
            $table->string('sender_passenger_fullname');
            $table->string('sender_telephone');
            $table->string('r_document_id');
            $table->string('recipient_number_identity_document');
            $table->string('recipient_passenger_fullname');
            $table->string('recipient_telephone');
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
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
        Schema::dropIfExists('document_transport_dispatches');
    }
}

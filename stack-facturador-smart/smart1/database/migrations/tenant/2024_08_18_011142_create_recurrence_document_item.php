<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecurrenceDocumentItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_recurrence_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('document_recurrence_id');
            $table->date('emission_date');
            $table->time('emission_time');
            $table->boolean('emitted')->default(false);
            $table->boolean('email_sent')->default(false);
            $table->boolean('whatsapp_sent')->default(false);
            $table->boolean('active')->default(true);
            $table->foreign('document_recurrence_id')->references('id')->on('document_recurrence');
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
        Schema::dropIfExists('document_recurrence_items');
    }
}

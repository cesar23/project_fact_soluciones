<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecurrenceDocument extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_recurrence', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('document_id');
            $table->enum('interval', ['daily', 'weekly', 'biweekly', 'monthly', 'bimonthly', 'quarterly', 'semiannual', 'annual']);
            $table->boolean('send_email')->default(false);
            $table->boolean('send_whatsapp')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->foreign('document_id')->references('id')->on('documents');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_recurrence');
    }
}

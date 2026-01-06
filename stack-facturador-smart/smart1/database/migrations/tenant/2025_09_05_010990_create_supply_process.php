<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplyProcess extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supply_process', function (Blueprint $table) {
            $table->increments('id');
            $table->string('record');
            $table->unsignedInteger('user_id');
            $table->unsignedBigInteger('supply_id')->nullable();
            $table->string('document')->nullable();
            $table->date('document_date');
            $table->date('receive_date');
            $table->date('assign_date')->nullable();
            $table->string('year')->nullable();
            $table->string('subject');
            $table->unsignedInteger('supply_office_id')->nullable();
            $table->string('state')->default('0');
            $table->string('location',500)->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('observation_document')->nullable();
            $table->text('observation_finish')->nullable();
            $table->string('n_folios')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supply_process');
    }

    
}

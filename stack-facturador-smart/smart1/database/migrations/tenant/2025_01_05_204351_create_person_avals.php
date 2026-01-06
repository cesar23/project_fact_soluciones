<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204351_create_persons_aval
class CreatePersonAvals extends Migration
{
    public function up()
    {
        Schema::create('person_avals', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number');
            $table->string('name');
            $table->string('trade_name')->nullable();
            $table->string('identity_document_type_id');
            $table->string('address')->nullable();
            $table->string('telephone')->nullable();
            $table->char('country_id', 2)->nullable();
            $table->json('location_id')->nullable();
            $table->unsignedInteger('person_id')->nullable();
            $table->timestamps();
            $table->foreign('person_id')->references('id')->on('persons');
            $table->foreign('identity_document_type_id')->references('id')->on('cat_identity_document_types');

            $table->foreign('country_id')->references('id')->on('countries');
            
        });
    }

    public function down()
    {
        Schema::dropIfExists('person_avals');
    }
}

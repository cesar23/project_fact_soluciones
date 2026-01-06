<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonDispatchers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('person_dispatchers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('identity_document_type_id')->nullable();
            $table->string('number')->nullable();
            $table->string('name');
            $table->string('telephone')->nullable();
            $table->boolean('active')->default(true);
            $table->foreign('identity_document_type_id')->references('id')->on('cat_identity_document_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('person_dispatchers');
    }
}

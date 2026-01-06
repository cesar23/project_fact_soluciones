<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNameDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('name_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->string('sale_note', 200)->nullable();
            $table->string('orden_note', 200)->nullable();
            $table->string('quotation', 200)->nullable();
            $table->string('sale_opportunity', 200)->nullable();
            $table->string('technical_service', 200)->nullable();
            $table->string('contract', 200)->nullable();
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
        Schema::dropIfExists('name_documents');
    }
}

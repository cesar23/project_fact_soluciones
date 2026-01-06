<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204243_create_sunat_state_type_document
class CreateSunatStateTypeDocument extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('sunat_document_state')) {
            Schema::create('sunat_document_state', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('document_id');
                $table->char('state_type_id', 2);
                $table->tinyInteger('attempts')->default(0);
                $table->date('state_date')->nullable();
                $table->foreign('state_type_id')->references('id')->on('state_types');
                $table->foreign('document_id')->references('id')->on('documents');
                $table->timestamps();
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
        Schema::dropIfExists('sunat_document_state');
    }
}


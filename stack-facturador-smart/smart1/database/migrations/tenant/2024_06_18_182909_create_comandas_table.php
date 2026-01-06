<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComandasTable extends Migration
{
    public function up()
    {
        Schema::create('comandas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('codigo');
            // Otros campos relevantes
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('comandas');
    }
}
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNameSuscription extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('name_suscription', function (Blueprint $table) {
            $table->increments('id');
            $table->string('parents', 200)->default('Padres')->nullable();
            $table->string('children', 200)->default('Hijos')->nullable();
            $table->string('grades', 200)->default('Grados')->nullable();
            $table->string('sections', 200)->default('Secciones')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('name_suscription');
    }
}

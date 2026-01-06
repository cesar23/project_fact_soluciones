<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_06_26_204245_create_certificates_module
class CreateCertificatesModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->text('tag_1',500)->comment('Nombre de la plantilla')->nullable();
            $table->text('tag_3')->comment('Curso que se ha realizado')->nullable();
            $table->text('tag_4')->comment('Fecha de desarrollo, academia, dirección, etc.')->nullable();
            $table->string('tag_5')->comment('Titulo puntos')->nullable();
            $table->string('tag_6',500)->comment('Fecha y lugar de emisión')->nullable();
            $table->string('tag_7',500)->comment('Resolución')->nullable();
            $table->string('external_id')->nullable();
            $table->boolean('active')->default(true);
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
        Schema::dropIfExists('certificates');
    }
}


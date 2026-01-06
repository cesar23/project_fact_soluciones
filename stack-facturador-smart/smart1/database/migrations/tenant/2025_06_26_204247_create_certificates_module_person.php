<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_06_26_204247_create_certificates_module_person
class CreateCertificatesModulePerson extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('certificates_person', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certificate_id')->nullable()->constrained('certificates');
            $table->text('tag_1')->comment('Nombre de la persona')->nullable();
            $table->text('tag_2')->comment('Número de documento de la persona')->nullable();
            $table->text('tag_3')->comment('Curso que se ha realizado')->nullable();
            $table->text('tag_4')->comment('Fecha de desarrollo, academia, dirección, etc.')->nullable();
            $table->string('tag_5')->comment('Titulo puntos')->nullable();
            $table->string('tag_6',500)->comment('Fecha y lugar de emisión')->nullable();
            $table->string('tag_7',500)->comment('Resolución')->nullable();
            $table->json('items')->nullable();
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
        Schema::dropIfExists('certificates_person');
    }
}


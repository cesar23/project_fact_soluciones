<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2024_08_18_011175_create_massive_nessages




class CreateMassiveMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('massive_messages')) {
            Schema::create('massive_messages', function (Blueprint $table) {
                $table->increments('id');
                $table->enum('type', ['email', 'whatsapp'])->comment('Tipo de mensaje');
                $table->string('subject')->nullable()->comment('Asunto del mensaje (solo para correo)');
                $table->text('body')->comment('Cuerpo del mensaje');
                $table->json('attachments')->nullable()->comment('Rutas de archivos adjuntos en formato JSON');
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
        Schema::dropIfExists('massive_messages');
    }
}

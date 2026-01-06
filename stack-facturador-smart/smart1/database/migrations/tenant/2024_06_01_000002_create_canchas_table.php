<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCanchasTable extends Migration
{
    public function up()
    {
        Schema::create('canchas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('ubicacion');
            $table->integer('capacidad');
            $table->string('reservante_nombre');
            $table->string('reservante_apellidos');
            $table->time('hora_reserva');
            $table->date('fecha_reserva');
            $table->integer('tiempo_reserva');
            $table->string('ticket')->unique();
            $table->string('qr_code_path')->nullable(); // Nuevo campo para la ruta del código QR
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('canchas');
    }
}

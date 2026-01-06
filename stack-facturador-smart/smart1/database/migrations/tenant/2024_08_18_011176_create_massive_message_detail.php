<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2024_08_18_011176_create_,massive_message_detail




class CreateMassiveMessageDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('massive_message_detail')) {
            Schema::create('massive_message_detail', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('massive_message_id')->constrained('massive_messages')->onDelete('cascade')->comment('ID del mensaje');
                $table->unsignedInteger('person_id')->constrained('persons')->onDelete('cascade')->comment('ID del destinatario');
                $table->enum('status', ['pending', 'sent', 'failed'])->default('pending')->comment('Estado del envío');
                $table->text('error_message')->nullable()->comment('Detalle del error si falla');
                $table->unsignedSmallInteger('attempts')->default(0)->comment('Número de intentos realizados');
                $table->timestamp('last_attempt_at')->nullable()->comment('Fecha del último intento');
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
        Schema::dropIfExists('massive_message_detail');
    }
}

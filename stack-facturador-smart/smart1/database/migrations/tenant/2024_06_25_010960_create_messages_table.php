<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    public function up()
    {
        Schema::connection('tenant')->create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('receiver_id');
            $table->text('message');
            $table->timestamps();

            $table->foreign('sender_id')->references('id')->on('personal')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('personal')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::connection('tenant')->dropIfExists('messages');
    }
}


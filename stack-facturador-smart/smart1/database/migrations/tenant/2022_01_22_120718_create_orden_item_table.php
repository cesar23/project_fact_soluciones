<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdenItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('orden_item')) {
            Schema::create('orden_item', function (Blueprint $table) {
                $table->increments('id');
                $table->date('date');
                $table->string('observations');
                $table->unsignedInteger('food_id');
                $table->foreign('food_id')->references('id')->on('foods');
                $table->unsignedInteger('orden_id');
                $table->foreign('orden_id')->references('id')->on('ordens');
                $table->unsignedInteger('user_id');
                $table->foreign('user_id')->references('id')->on('users');
                $table->unsignedInteger('status_orden_id');
               $table->foreign('status_orden_id')->references('id')->on('status_orden');
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
        Schema::dropIfExists('orden_item');
    }
}

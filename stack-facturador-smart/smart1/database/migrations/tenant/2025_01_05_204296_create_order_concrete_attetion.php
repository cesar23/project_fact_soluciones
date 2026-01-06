<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//2025_01_05_204296_create_order_concrete_attetion

class CreateOrderConcreteAttetion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::create('order_concrete_attentions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('dispatch_note');
            $table->string('quantity')->nullable();
            $table->unsignedInteger('order_concrete_id');
            $table->foreign('order_concrete_id')->references('id')->on('order_concretes')->onDelete('cascade');
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
        //
        Schema::dropIfExists('order_concrete_attentions');
    }
}

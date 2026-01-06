<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthFoodDealer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('food_dealer_auth')) {
            Schema::create('food_dealer_auth', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedBigInteger('person_food_dealer_id');
                $table->unsignedInteger('auth_user_id');
                $table->tinyInteger('order');
                $table->timestamps();
                $table->foreign('person_food_dealer_id')->references('id')->on('person_food_dealer');
                $table->foreign('auth_user_id')->references('id')->on('users');
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
        Schema::dropIfExists('food_dealer_auth');
        
    }
}

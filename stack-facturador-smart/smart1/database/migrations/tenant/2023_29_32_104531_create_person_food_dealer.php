<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonFoodDealer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('person_food_dealer', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('person_id');
            $table->unsignedInteger('order_note_id');
            $table->date('date_of_issue');
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('person_id')->references('id')->on('persons');
            $table->foreign('order_note_id')->references('id')->on('order_notes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::dropIfExists('person_food_dealer');
    }
}

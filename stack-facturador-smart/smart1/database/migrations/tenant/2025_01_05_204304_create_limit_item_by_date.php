<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//2025_01_05_204304_create_limit_item_by_date

class CreateLimitItemByDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::create('limit_item_by_date', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('customer_id');
            $table->date('date');
            $table->integer('limit');
            $table->boolean('reached')->default(false);
            $table->timestamps();
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('customer_id')->references('id')->on('persons');
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
        Schema::dropIfExists('limit_item_by_date');
    }
}

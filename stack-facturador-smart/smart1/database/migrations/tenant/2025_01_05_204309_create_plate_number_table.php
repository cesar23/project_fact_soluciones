<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//2025_01_05_204309_create_plate_number_table

class CreatePlateNumberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::create('plate_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->string('year');
            $table->unsignedBigInteger('plate_number_brand_id');
            $table->foreign('plate_number_brand_id')->references('id')->on('plate_number_brands');
            $table->unsignedBigInteger('plate_number_model_id');
            $table->foreign('plate_number_model_id')->references('id')->on('plate_number_models');
            $table->unsignedBigInteger('plate_number_color_id');
            $table->foreign('plate_number_color_id')->references('id')->on('plate_number_colors');
            $table->unsignedBigInteger('plate_number_type_id');
            $table->foreign('plate_number_type_id')->references('id')->on('plate_number_types');
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
        Schema::dropIfExists('plate_numbers');
    }
}

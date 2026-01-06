<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//2025_01_05_204310_create_plate_number_km

class CreatePlateNumberKm extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::create('plate_number_kms', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->unsignedBigInteger('plate_number_id');
            $table->foreign('plate_number_id')->references('id')->on('plate_numbers');
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
        Schema::dropIfExists('plate_number_kms');
    }
}

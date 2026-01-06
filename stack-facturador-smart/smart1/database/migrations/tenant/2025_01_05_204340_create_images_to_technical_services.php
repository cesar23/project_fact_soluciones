<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204340_create_images_to_technical_services
class CreateImagesToTechnicalServices extends Migration
{
    public function up()
    {
        Schema::create('technical_services_images', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('technical_service_id');
            $table->string('image_path');
            $table->foreign('technical_service_id')->references('id')->on('technical_services');
        });
    }

    public function down()
    {
        Schema::dropIfExists('technical_services_images');
    }
}

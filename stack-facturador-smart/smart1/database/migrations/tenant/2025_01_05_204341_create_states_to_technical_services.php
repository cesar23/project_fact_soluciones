<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204341_create_states_to_technical_services
class CreateStatesToTechnicalServices extends Migration
{
    public function up()
    {
        Schema::create('technical_services_states', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('technical_services_states');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2024_08_18_011188_create_module_level_plans

class CreateModuleLevelPlans extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('module_level_plans', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('module_level_id');
            $table->unsignedInteger('plan_id');
            $table->foreign('module_level_id')->references('id')->on('module_levels');
            $table->foreign('plan_id')->references('id')->on('plans');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('module_level_plans');
    }
}

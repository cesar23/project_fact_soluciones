<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_09_05_010984_create_supply_contract
class CreateSupplyContract extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supply_contract', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('supply_solicitude_id');
            $table->unsignedInteger('person_id');
            $table->unsignedBigInteger('supplie_plan_id');
            $table->unsignedBigInteger('supply_id');
            $table->string('path_solicitude')->nullable();
            $table->unsignedInteger('supply_service_id');
            $table->string('address')->nullable();
            $table->date('install_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('finish_date')->nullable();
            $table->boolean('active')->default(true);
            $table->text('observation')->nullable();
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
        Schema::dropIfExists('supply_service');
    }

    
}

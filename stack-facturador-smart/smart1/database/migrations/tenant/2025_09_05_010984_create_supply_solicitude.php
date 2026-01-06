<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_09_05_010984_create_supply_solicitude
class CreateSupplySolicitude extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supply_solicitude', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('person_id');
            $table->unsignedBigInteger('supply_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('supply_service_id')->nullable();
            $table->date('program_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('finish_date')->nullable();
            $table->string('use')->nullable();
            $table->boolean('active')->default(true);
            $table->tinyInteger('review')->default(0);
            $table->tinyInteger('cod_tipo')->default(1);
            $table->unsignedBigInteger('supply_debt_id')->nullable();
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

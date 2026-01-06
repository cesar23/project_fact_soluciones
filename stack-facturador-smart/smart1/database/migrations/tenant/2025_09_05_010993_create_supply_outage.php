<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_09_05_010993_create_supply_outage
class CreateSupplyOutage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supply_outage', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('supply_contract_id');
            $table->text('observation')->nullable();
            $table->string('state')->nullable();
            $table->unsignedInteger('person_id');
            $table->text('type')->nullable();
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
        Schema::dropIfExists('supply_outage');
    }

    
}

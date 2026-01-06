<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//2025_10_14_000002_create_weapon_tracking
class CreateWeaponTracking extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::create('weapon_tracking', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('person_id');
            $table->unsignedInteger('item_lot_id')->nullable();
            $table->date('date_of_issue');
            $table->time('time_of_issue');
            $table->string('type');
            $table->string('destiny',500);
            $table->string('observation',500)->nullable();
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('person_id')->references('id')->on('persons');
            $table->foreign('item_lot_id')->references('id')->on('item_lots');
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
        Schema::dropIfExists('weapon_tracking');
    
    }
}

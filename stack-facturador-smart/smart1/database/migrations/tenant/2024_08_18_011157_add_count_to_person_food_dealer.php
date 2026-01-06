<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddCountToPersonFoodDealer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('person_food_dealer', function ($table) {
            $table->tinyInteger('delivered')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('person_food_dealer', function ($table) {
            $table->dropColumn('delivered');
        });
    }
}

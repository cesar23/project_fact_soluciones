<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


//2025_01_05_204268_add_configuration_range_item_unit_type

class AddConfigurationRangeItemUnitType extends Migration
{
    public function up()
    {

        Schema::table('configurations', function (Blueprint $table) {
            $table->boolean('range_item_unit_type')->default(false);
        });

    


    }

    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('range_item_unit_type');
        });

    }


}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


//2025_01_05_204267_add_item_unit_type_range

class AddItemUnitTypeRange extends Migration
{
    public function up()
    {

        Schema::table('item_unit_types', function (Blueprint $table) {
            $table->decimal('range_min', 12, 2)->nullable();
            $table->decimal('range_max', 12, 2)->nullable();
        });

    


    }

    public function down()
    {
        Schema::table('item_unit_types', function (Blueprint $table) {
            $table->dropColumn('range_min');
            $table->dropColumn('range_max');
        });

    }


}
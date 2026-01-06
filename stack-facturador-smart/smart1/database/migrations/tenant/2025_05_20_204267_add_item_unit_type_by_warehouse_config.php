<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_05_20_204267_add_item_unit_type_by_warehouse_config
class AddItemUnitTypeByWarehouseConfig extends Migration
{
    public function up()
    {
        
        if (!Schema::hasColumn('configurations', 'item_unit_type_by_warehouse')) {
            Schema::table('configurations', function (Blueprint $table) {
                $table->boolean('item_unit_type_by_warehouse')->default(false); 
            });
        }

    }

    public function down()
    {
        if (Schema::hasColumn('configurations', 'item_unit_type_by_warehouse')) {
            Schema::table('configurations', function (Blueprint $table) {
                $table->dropColumn('item_unit_type_by_warehouse');
            });
        }
    }

}
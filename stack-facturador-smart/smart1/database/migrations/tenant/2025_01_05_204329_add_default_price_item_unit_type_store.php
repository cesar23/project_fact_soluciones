<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204329_add_default_price_item_unit_type_store
class AddDefaultPriceItemUnitTypeStore extends Migration
{
    public function up()
    {
        Schema::table('item_unit_types', function (Blueprint $table) {
            $table->boolean('default_price_store')->default(false);
        });
        
    }

    public function down()
    {
        Schema::table('item_unit_types', function (Blueprint $table) {
            $table->dropColumn('default_price_store');
        });
    }
}

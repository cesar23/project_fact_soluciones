<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//2025_10_07_2024341_add_property_state_to_item_properties
class AddPropertyStateToItemProperties extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('item_properties', 'state')) {
            Schema::table('item_properties', function (Blueprint $table) {
                $table->string('state')->default('Activo');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('item_properties', 'state')) {
            Schema::table('item_properties', function (Blueprint $table) {
                $table->string('state')->default('Activo')->change();
            });
        }
    }
}

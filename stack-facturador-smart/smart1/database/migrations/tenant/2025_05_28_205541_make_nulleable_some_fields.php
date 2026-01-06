<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

//2025_05_28_205541_make_nulleable_some_fields
class MakeNulleableSomeFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->unsignedInteger('area_id')->nullable()->change();
        });

        Schema::table('ordens', function (Blueprint $table) {
            $table->unsignedInteger('table_id')->nullable()->change();
        });
        Schema::table('orden_item', function (Blueprint $table) {
            $table->unsignedInteger('area_id')->nullable()->change();
        });

        if (!Schema::hasColumn('orden_item', 'item_id')) {
            Schema::table('orden_item', function (Blueprint $table) {
                $table->unsignedInteger('item_id')->nullable();
                $table->foreign('item_id')->references('id')->on('items');
            });
        }

        if (Schema::hasColumn('orden_item', 'food_id')) {
            Schema::table('orden_item', function (Blueprint $table) {
                $table->dropForeign(['food_id']);
                $table->dropColumn('food_id');
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
        
    }
}

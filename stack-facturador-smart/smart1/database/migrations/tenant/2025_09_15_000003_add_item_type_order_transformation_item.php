<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_09_15_000003_add_item_type_order_transformation_item
class AddItemTypeOrderTransformationItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('order_transformation_items', 'item_type')) {
            Schema::table('order_transformation_items', function (Blueprint $table) {
                $table->string('item_type')->after('status')->default('raw_material');
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
        Schema::table('order_transformation_items', function (Blueprint $table) {
            $table->dropColumn('item_type');
        });
    }
}
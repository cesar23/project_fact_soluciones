<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_01_02_204229_modify_stock_item_direct
class ModifyStockItemDirect extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('configurations', 'modify_stock_item_direct')) {
            Schema::table('configurations', function (Blueprint $table) {
                $table->boolean('modify_stock_item_direct')->default(false);
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
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('modify_stock_item_direct');
        });
    }
}

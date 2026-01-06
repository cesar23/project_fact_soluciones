<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

//2024_12_13_0120110_add_configuration_item_lot_warehouse.php

class AddConfigurationItemLotWarehouse extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('configurations', 'item_lots_group_filter_by_warehouse')) {
            Schema::table('configurations', function (Blueprint $table) {
                $table->boolean('item_lots_group_filter_by_warehouse')->default(false);
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
            $table->dropColumn('item_lots_group_filter_by_warehouse');
        });
    }
}

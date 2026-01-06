<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
//2025_07_16_153358_add_config_to_show_restrict_stock_in_items_form
class AddConfigToShowRestrictStockInItemsForm extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
        Schema::table('configurations', function (Blueprint $table) {
            $table->boolean('show_restrict_stock_in_items_form')->default(false);
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('show_restrict_stock_in_items_form');
        });
    }
}

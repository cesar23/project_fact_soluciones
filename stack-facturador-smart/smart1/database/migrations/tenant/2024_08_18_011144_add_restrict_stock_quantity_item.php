<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRestrictStockQuantityItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('item_warehouse', function (Blueprint $table) {
            $table->decimal('restrict_stock_quantity', 12, 2)->nullable()->after('stock');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_warehouse', function (Blueprint $table) {
            $table->dropColumn('restrict_stock_quantity');
        });
    
    }
}

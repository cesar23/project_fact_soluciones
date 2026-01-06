<?php

use App\Models\Tenant\Document;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_07_16_153337_add_warehouse_price_to_item_set
class AddWarehousePriceToItemSet extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('configurations', 'item_set_warehouse_price')) {
            Schema::table('configurations', function (Blueprint $table) {
                $table->boolean('item_set_warehouse_price')->default(false);
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
            $table->dropColumn('item_set_warehouse_price');
        });
    }
}

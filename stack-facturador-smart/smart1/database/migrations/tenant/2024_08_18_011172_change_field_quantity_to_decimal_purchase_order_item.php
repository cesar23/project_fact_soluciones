<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2024_08_18_011172_change_field_quantity_to_decimal_purchase_order_item


class ChangeFieldQuantityToDecimalPurchaseOrderItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('purchase_order_items', 'quantity')) {
            Schema::table('purchase_order_items', function (Blueprint $table) {
                $table->decimal('quantity', 12, 4)->change();
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
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShowExtraFieldsInvoice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->boolean('show_purchase_unit_price')->default(false);
            $table->boolean('show_item_discounts')->default(false);
            $table->boolean('show_item_stock')->default(false);
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
            $table->dropColumn('show_purchase_unit_price');
            $table->dropColumn('show_item_discounts');
            $table->dropColumn('show_item_stock');
        });
    }
}

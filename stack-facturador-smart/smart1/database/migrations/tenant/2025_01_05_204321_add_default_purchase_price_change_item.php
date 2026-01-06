<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204321_add_default_purchase_price_change_item
class AddDefaultPurchasePriceChangeItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
            Schema::table('configurations', function (Blueprint $table) {
                $table->boolean('default_purchase_price_change_item')->default(true);
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
            $table->dropColumn('default_purchase_price_change_item');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2024_08_18_011163_add_seller_can_ajust_inventory_configuration


class AddSellerCanAjustInventoryConfiguration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('configurations', 'seller_can_ajust_inventory')) {
            Schema::table('configurations', function (Blueprint $table) {
                $table->boolean('seller_can_ajust_inventory')->default(false);
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
            $table->dropColumn('seller_can_ajust_inventory');
        });
    }
}

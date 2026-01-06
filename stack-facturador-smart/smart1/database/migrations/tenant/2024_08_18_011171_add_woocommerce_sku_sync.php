<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2024_08_18_011171_add_woocommerce_sku_sync


class AddWoocommerceSkuSync extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('configuration_ecommerce', 'woocommerce_sku_sync')) {
            Schema::table('configuration_ecommerce', function (Blueprint $table) {
                $table->boolean('woocommerce_sku_sync')->default(true);
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
        Schema::table('configuration_ecommerce', function (Blueprint $table) {
            $table->dropColumn('woocommerce_sku_sync');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2024_08_18_011167_create_woocommerce_configuration

class CreateWoocommerceConfiguration extends Migration

{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('configuration_ecommerce', function (Blueprint $table) {

            $table->text('woocommerce_api_url')->nullable();
            $table->text('woocommerce_api_key')->nullable();
            $table->text('woocommerce_api_secret')->nullable();
            $table->text('woocommerce_api_version')->nullable();
            $table->text('woocommerce_api_last_sync')->nullable();
            $table->integer('last_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('configuration_ecommerce', function (Blueprint $table) {
            $table->dropColumn('woocommerce_api_url');
            $table->dropColumn('woocommerce_api_key');
            $table->dropColumn('woocommerce_api_secret');
            $table->dropColumn('woocommerce_api_version');
            $table->dropColumn('woocommerce_api_last_sync');
            $table->dropColumn('last_id');
        });
    }
}

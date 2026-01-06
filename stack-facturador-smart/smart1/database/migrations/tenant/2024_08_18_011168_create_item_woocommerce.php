<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2024_08_18_011168_create_item_woocommerce


class CreateItemWoocommerce extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('woocommerce_item')) {
            Schema::create('woocommerce_item', function (Blueprint $table) {

                $table->increments('id');
                $table->unsignedInteger('item_id');
                $table->unsignedInteger('woocommerce_item_id');
                $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
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
        Schema::dropIfExists('item_woocommerce');
    }
}

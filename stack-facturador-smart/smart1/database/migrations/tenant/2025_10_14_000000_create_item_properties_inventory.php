<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

//2025_10_14_000000_create_item_properties_inventory
class CreateItemPropertiesInventory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('item_properties_inventory')) {
            Schema::create('item_properties_inventory', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('item_property_id');
                $table->unsignedInteger('inventory_id');
                $table->timestamps();
                $table->foreign('item_property_id')->references('id')->on('item_properties');
                $table->foreign('inventory_id')->references('id')->on('inventories');
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
        Schema::dropIfExists('item_properties_inventory');
    }
}

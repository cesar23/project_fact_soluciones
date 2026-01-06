<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePendingItemCostResets extends Migration
{
    public function up()
    {
        Schema::create('pending_item_cost_resets', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('item_id');
            $table->unsignedInteger('warehouse_id');
            $table->dateTime('pending_from_date');
            $table->timestamps();

            $table->unique(['item_id', 'warehouse_id']); 
            $table->index(['item_id', 'warehouse_id', 'pending_from_date'], 'idx_item_wh_date');
            
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pending_item_cost_resets');
    }
}

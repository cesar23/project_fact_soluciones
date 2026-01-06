<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateValidationWarehouseItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('validation_warehouse_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('validation_warehouse_id');
            $table->unsignedInteger('item_id');
            $table->decimal('quantity', 12, 2);
            $table->decimal('stock', 12, 2);
            $table->json('lots')->nullable();
            $table->json('lots_not_count')->nullable();
            $table->foreign('validation_warehouse_id')->references('id')->on('validation_warehouse');
            $table->foreign('item_id')->references('id')->on('items');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('validation_warehouse_items');
    }
}

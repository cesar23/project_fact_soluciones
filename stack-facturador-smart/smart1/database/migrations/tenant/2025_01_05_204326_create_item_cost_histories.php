<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemCostHistories extends Migration
{
    public function up()
    {
        Schema::create('item_cost_histories', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('item_id');
            $table->unsignedInteger('warehouse_id');
            $table->dateTime('date');

            $table->decimal('quantity', 15, 6); // Nueva columna
            $table->enum('type', ['in', 'out']); // Nueva columna

            $table->decimal('average_cost', 15, 6)->default(0);
            $table->decimal('stock', 15, 6)->default(0);
            $table->decimal('unit_price', 15, 6)->default(0);


            $table->unsignedInteger('inventory_kardex_id')->nullable();

            // Relación polimórfica al documento original
            $table->unsignedInteger('inventory_kardexable_id')->nullable();
            $table->string('inventory_kardexable_type')->nullable();

            $table->timestamps();

            $table->index(['item_id', 'warehouse_id', 'date']);
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('item_cost_histories');
    }
}

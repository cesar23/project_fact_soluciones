<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeparatedStockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('separated_stock', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('warehouse_id');
            $table->unsignedInteger('order_transformation_id');
            $table->decimal('quantity', 12, 4)->default(0);
            $table->string('description')->default('Stock separado para transformación');
            $table->timestamps();

            $table->index(['item_id', 'warehouse_id']);
            $table->index('order_transformation_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('separated_stock');
    }
}
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCostAverage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('cost_average', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('purchase_id');
            $table->date('purchase_date');
            $table->decimal('purchase_cost', 12, 4);
            $table->decimal('total_purchase_cost', 12, 4);
            $table->decimal('purchase_quantity', 12, 4);
            $table->decimal('stock_without_purchase', 12, 4);
            $table->timestamps();
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('purchase_id')->references('id')->on('purchases');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cost_average');
    }
}

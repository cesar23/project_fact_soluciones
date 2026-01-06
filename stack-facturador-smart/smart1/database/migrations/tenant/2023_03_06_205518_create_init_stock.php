<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInitStock extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('init_stock')) {
            Schema::create('init_stock', function (Blueprint $table) {
                $table->increments('id');
                $table->date('init_date');
                $table->decimal('stock');
                $table->unsignedInteger('warehouse_id')->nullable();
                $table->foreign('warehouse_id')->references('id')->on('warehouses');
                $table->unsignedInteger('item_id');
                $table->foreign('item_id')->references('id')->on('items');
                $table->timestamps();
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
        Schema::dropIfExists('init_stock');
    }
}

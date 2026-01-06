<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTransformationItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_transformation_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_transformation_id');
            $table->unsignedInteger('item_id');
            $table->decimal('quantity', 12, 4);
            $table->decimal('unit_price', 12, 4)->nullable();
            $table->string('lot_code')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();

            $table->foreign('order_transformation_id')->references('id')->on('order_transformations')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_transformation_items');
    }
}
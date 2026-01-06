<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_09_29_210001_create_price_adjustment_items.php

class CreatePriceAdjustmentItems extends Migration
{
    public function up()
    {
        Schema::create('price_adjustment_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('price_adjustment_id');
            $table->unsignedInteger('item_id')->nullable();
            $table->unsignedInteger('brand_id')->nullable();
            $table->unsignedInteger('category_id')->nullable();
            $table->decimal('old_price', 12, 2)->nullable();
            $table->decimal('new_price', 12, 2)->nullable();
            $table->timestamps();

            $table->foreign('price_adjustment_id')->references('id')->on('price_adjustments')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('price_adjustment_items');
    }
}
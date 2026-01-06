<?php

use App\Models\Tenant\DocumentColumn;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApportionmentItemsStock extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apportionment_items_stock', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('purchase_item_id');
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('warehouse_id')->nullable();
            $table->decimal('stock', 10, 2);
            $table->decimal('stock_remaining', 10, 2);
            $table->decimal('unit_price_apportioned', 10, 2);
            $table->text('observation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('apportionment_items_stock');
    }
}

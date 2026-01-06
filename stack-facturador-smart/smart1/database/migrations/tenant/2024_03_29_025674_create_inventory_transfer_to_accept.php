<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryTransferToAccept extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_transfer_to_accept', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('inventory_transfer_id');
            $table->unsignedInteger('item_id');
            $table->decimal('quantity', 12, 2);
            $table->json('series_lots');
            $table->foreign('inventory_transfer_id')->references('id')->on('inventories_transfer');
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
        Schema::dropIfExists('inventory_transfer_to_accept');
    }
}

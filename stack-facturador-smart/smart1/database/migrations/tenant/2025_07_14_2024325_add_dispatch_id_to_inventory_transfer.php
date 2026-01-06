<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_07_14_2024325_add_dispatch_id_to_inventory_transfer
class AddDispatchIdToInventoryTransfer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
            Schema::table('inventories_transfer', function (Blueprint $table) {
                $table->unsignedInteger('dispatch_id')->nullable();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventories_transfer', function (Blueprint $table) {
            $table->dropColumn('dispatch_id');
        });
    }
}

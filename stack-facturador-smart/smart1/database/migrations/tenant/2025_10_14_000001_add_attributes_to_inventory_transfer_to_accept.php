<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//2025_10_14_000001_add_attributes_to_inventory_transfer_to_accept
class AddAttributesToInventoryTransferToAccept extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('inventory_transfer_to_accept', function (Blueprint $table) {
            $table->json('attributes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_transfer_to_accept', function (Blueprint $table) {
            $table->dropColumn('attributes');
        });
    
    }
}

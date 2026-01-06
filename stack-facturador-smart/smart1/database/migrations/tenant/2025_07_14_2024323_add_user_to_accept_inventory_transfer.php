<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_07_14_2024323_add_user_to_accept_inventory_transfer
class AddUserToAcceptInventoryTransfer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
            Schema::table('inventories_transfer', function (Blueprint $table) {
                $table->unsignedInteger('user_accept_id')->nullable();
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
            $table->dropColumn('user_accept_id');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_06_26_204243_add_permission_users
class AddPermissionUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('create_order_delivery')->default(true);
            $table->boolean('edit_order_delivery')->default(true);
            $table->boolean('voided_order_delivery')->default(true);
            $table->boolean('voided_cpe')->default(true);
            $table->boolean('note_cpe')->default(true);
            $table->boolean('voided_sale_note')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('create_order_delivery');
            $table->dropColumn('edit_order_delivery');
            $table->dropColumn('voided_order_delivery');
            $table->dropColumn('voided_cpe');
            $table->dropColumn('note_cpe');
            $table->dropColumn('voided_sale_note');
        });
    }
}


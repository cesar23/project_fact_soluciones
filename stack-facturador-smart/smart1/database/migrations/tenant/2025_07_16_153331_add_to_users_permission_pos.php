<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

//2025_07_16_153331_add_to_users_permission_pos
class AddToUsersPermissionPos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('edit_pos')->default(false);
            $table->boolean('reopen_pos')->default(false);
            $table->boolean('delete_pos')->default(false);
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
            $table->dropColumn('edit_pos');
            $table->dropColumn('reopen_pos');
            $table->dropColumn('delete_pos');
        });
    }
}

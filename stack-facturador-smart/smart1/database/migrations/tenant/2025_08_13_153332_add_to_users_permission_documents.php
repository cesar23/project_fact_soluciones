<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
//2025_08_13_153332_add_to_users_permission_documents
class AddToUsersPermissionDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('show_packers_in_document')->default(true);
            $table->boolean('show_dispatchers_in_document')->default(true);
            $table->boolean('show_box_in_document')->default(true);
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
            $table->dropColumn('show_packers_in_document');
            $table->dropColumn('show_dispatchers_in_document');
            $table->dropColumn('show_box_in_document');
        });
    }
}

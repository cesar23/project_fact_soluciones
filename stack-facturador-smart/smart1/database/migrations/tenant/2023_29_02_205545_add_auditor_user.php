<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAuditorUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('auditor')->default(false);
        });
        Schema::table('documents', function (Blueprint $table) {
            $table->boolean('auditor_state')->default(false);
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
            $table->dropColumn('auditor');

        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('auditor_state');
        });
    }
}

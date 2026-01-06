<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCredencialToClients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasColumn('clients', 'users')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->string('users')->nullable()->after('token');
            });
        }

        if (!Schema::hasColumn('clients', 'password')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->string('password')->nullable()->after('token');
            });
        }

        if (!Schema::hasColumn('clients', 'password_cdt')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->string('password_cdt')->nullable()->after('token');
            });
        }
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            //
        });
    }
}

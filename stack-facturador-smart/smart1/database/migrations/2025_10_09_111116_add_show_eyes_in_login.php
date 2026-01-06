<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

//2025_10_09_111116_add_show_eyes_in_login


class AddShowEyesInLogin extends Migration
{
    /**
     * Run the migrations.




     *
     * @return void
     */

    public function up()
    {
    
        Schema::table('clients', function (Blueprint $table) {
            $table->boolean('show_eyes_in_login')->default(false);
        });



    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('show_eyes_in_login');
        });
    }
}

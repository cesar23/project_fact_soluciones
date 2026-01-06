<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

//2025_07_16_111114_add_is_secondary_to_users


class AddIsSecondaryToUsers extends Migration
{
    /**
     * Run the migrations.




     *
     * @return void
     */

    public function up()
    {
    
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_secondary')->default(false);
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
            $table->dropColumn('is_secondary');
        });



    }
}

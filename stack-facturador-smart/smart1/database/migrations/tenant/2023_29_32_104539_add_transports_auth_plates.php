<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransportsAuthPlates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transports',function (Blueprint $table){
            $table->string('auth_plate_primary')->nullable();
            $table->string('auth_plate_secondary')->nullable();
        });

    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        Schema::table('transports',function (Blueprint $table){
            $table->dropColumn('auth_plate_primary');
            $table->dropColumn('auth_plate_secondary');
        });
    }
}

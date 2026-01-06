<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIntegrateSystemTypeUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users',function (Blueprint $table){
            $table->unsignedInteger('integrate_user_type_id')->nullable();
            $table->foreign('integrate_user_type_id')->references('id')->on('integrate_user_type')->onDelete('cascade');
        });

    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users',function (Blueprint $table){
            $table->dropForeign(['integrate_user_type_id']);
            $table->dropColumn('integrate_user_type_id');
        });

    }
}

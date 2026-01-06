<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204355_add_to_user_casher_id
class AddToUserCasherId extends Migration 
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('user_cash_id')->nullable();
            $table->foreign('user_cash_id')->references('id')->on('users');

            
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('user_cash_id');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204271_show_in_pos_account_bank

class ShowInPosAccountBank extends Migration
{
    public function up()
    {

        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->boolean('show_in_pos')->default(false);
        });

    


    }

    public function down()
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropColumn('show_in_pos');
        });

    }


}
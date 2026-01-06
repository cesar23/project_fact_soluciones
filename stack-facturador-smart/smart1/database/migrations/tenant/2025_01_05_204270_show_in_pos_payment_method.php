<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204270_show_in_pos_payment_method

class ShowInPosPaymentMethod extends Migration
{
    public function up()
    {

        Schema::table('payment_method_types', function (Blueprint $table) {
            $table->boolean('show_in_pos')->default(false);
        });

    


    }

    public function down()
    {
        Schema::table('payment_method_types', function (Blueprint $table) {
            $table->dropColumn('show_in_pos');
        });

    }


}
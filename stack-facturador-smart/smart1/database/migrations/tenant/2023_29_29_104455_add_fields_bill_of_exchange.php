<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsBillOfExchange extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::table('bills_of_exchange_payments', function (Blueprint $table) {
            $table->boolean('total_canceled')->default(false);
        });

    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

       
        Schema::table('bills_of_exchange_payments', function (Blueprint $table) {
            $table->dropColumn('total_canceled');
        });
    
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204254_dispatch_total_weight_3
class DispatchTotalWeight3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::table('dispatches', function (Blueprint $table) {
            $table->decimal('total_weight', 10, 3)->change();
        });
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('dispatches', function (Blueprint $table) {
            $table->decimal('total_weight', 10, 2)->change();
        });
      
    }
}

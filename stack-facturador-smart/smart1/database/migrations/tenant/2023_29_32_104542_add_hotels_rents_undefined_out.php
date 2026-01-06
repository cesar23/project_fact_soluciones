<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHotelsRentsUndefinedOut extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hotel_rents',function (Blueprint $table){
            $table->boolean('undefined_out')->default(false);
        });

    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        Schema::table('hotel_rents',function (Blueprint $table){
            $table->dropColumn('undefined_out');
        });
      
       
    }
}

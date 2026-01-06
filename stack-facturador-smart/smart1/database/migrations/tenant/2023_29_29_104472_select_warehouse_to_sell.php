<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SelectWarehouseToSell extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      

            Schema::table('configurations', function(Blueprint $table){
                $table->boolean('select_warehouse_to_sell')->default(false);
            });
         
     
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
            
    
            Schema::table('configurations', function(Blueprint $table){
                $table->dropColumn('select_warehouse_to_sell');
            });

      
    }
}

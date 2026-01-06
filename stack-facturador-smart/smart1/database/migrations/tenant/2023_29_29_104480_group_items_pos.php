<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GroupItemsPos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      

            Schema::table('configurations', function(Blueprint $table){
                $table->boolean('group_items')->default(true);

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
                $table->dropColumn('group_items');
               

            });

      
    }
}

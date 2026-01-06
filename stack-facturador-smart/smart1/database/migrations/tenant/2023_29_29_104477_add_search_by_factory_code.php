<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSearchByFactoryCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      

            Schema::table('configurations', function(Blueprint $table){
                $table->boolean('search_by_factory_code')->default(false);

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
                $table->dropColumn('search_by_factory_code');
               

            });

      
    }
}

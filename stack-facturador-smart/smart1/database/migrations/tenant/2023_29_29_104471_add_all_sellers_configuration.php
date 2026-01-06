<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAllSellersConfiguration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      

            Schema::table('configurations', function(Blueprint $table){
                $table->boolean('all_sellers')->default(false);
                $table->boolean('all_products')->default(false);
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
                $table->dropColumn('all_sellers');
                $table->dropColumn('all_products');
            });

      
    }
}

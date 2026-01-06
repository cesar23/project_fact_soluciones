<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPurchasesControl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      

            Schema::table('configurations', function(Blueprint $table){
                $table->boolean('purchases_control')->default(false);
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
                $table->dropColumn('purchases_control');

            });

      
    }
}

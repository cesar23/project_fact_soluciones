<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDetraccionPurchase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      

            Schema::table('purchases', function(Blueprint $table){
                $table->string('const_detraccion')->nullable();
                $table->decimal('percentage_detraccion',12,2)->nullable();
                $table->date('date_detraccion')->nullable();

            });
         
     
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
            
    
            Schema::table('purchases', function(Blueprint $table){
                $table->dropColumn('const_detraccion');
                $table->dropColumn('percentage_detraccion');
                $table->dropColumn('date_detraccion');
               

            });

      
    }
}

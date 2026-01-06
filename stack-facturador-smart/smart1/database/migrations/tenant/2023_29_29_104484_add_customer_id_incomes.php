<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerIdIncomes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      

            Schema::table('income', function(Blueprint $table){
                $table->unsignedBigInteger('customer_id')->nullable()->after('customer');
            });
         
     
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
                
        
                Schema::table('income', function(Blueprint $table){
                    $table->dropColumn('customer_id');
                });
            
    

      
    }
}

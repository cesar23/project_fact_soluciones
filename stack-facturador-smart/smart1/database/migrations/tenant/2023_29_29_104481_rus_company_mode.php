<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RusCompanyMode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      

            Schema::table('companies', function(Blueprint $table){
                $table->boolean('is_rus')->default(false);

            });
         
     
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
            
    
            Schema::table('companies', function(Blueprint $table){
                $table->dropColumn('is_rus');
               

            });

      
    }
}

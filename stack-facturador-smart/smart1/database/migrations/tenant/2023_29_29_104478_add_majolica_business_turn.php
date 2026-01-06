<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddMajolicaBusinessTurn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      
        DB::connection('tenant')->table('business_turns')->insert([
         
            'value' => 'majolica',
            'name' => 'Mayólica',
            'active' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
           
         
     
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
            
        DB::connection('tenant')->table('business_turns')->where('value', 'majolica')->delete();
    
      
    }
}

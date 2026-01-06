<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddIntegrateSystemBusinessTurn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      
        DB::connection('tenant')->table('business_turns')->insert([
         
            'value' => 'integrate_system',
            'name' => 'Sistema Integrado',
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
            
        DB::connection('tenant')->table('business_turns')->where('value', 'integrate_system')->delete();
    
      
    }
}

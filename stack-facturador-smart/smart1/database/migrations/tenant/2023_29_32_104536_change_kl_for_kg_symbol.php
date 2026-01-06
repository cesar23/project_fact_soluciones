<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeKlForKgSymbol extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('tenant')
        ->table('unit_measure')
        ->where('code','KGM')
        ->update(['symbol' => 'KG']);

        DB::connection('tenant')
        ->table('cat_unit_types')
        ->where('id','KGM')
        ->update(['symbol' => 'KG']);
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        DB::connection('tenant')
        ->table('unit_measure')
        ->where('code','KGM')
        ->update(['symbol' => 'KL']);

        DB::connection('tenant')
        ->table('cat_unit_types')
        ->where('id','KGM')
        ->update(['symbol' => 'KL']);

      

    }
}

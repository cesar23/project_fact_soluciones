<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class InsertTneIfNotExist extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

       
 

        $data = ['id' => 'TNE', 'description' => 'Toneladas', 'symbol' => 'TNL', 'active' => 1, 'show_symbol' => 1];
        $exists = DB::table('cat_unit_types')->where('id', $data['id'])->count();
        if($exists == 0){
            DB::table('cat_unit_types')->insert($data);
        }
     
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}

<?php

use App\Models\Tenant\Dispatch;
use App\Models\Tenant\DispatchItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateTnUnitTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        //buscar si hay más de dos registros con el id TNE
        $exists = DB::table('cat_unit_types')->where('id', 'TNE')->count();
 

        $update = ['id' => 'TNE', 'description' => 'Toneladas', 'symbol' => 'TNL', 'active' => 1, 'show_symbol' => 1];
        DB::table('cat_unit_types')->where('id', 'TNE')->update($update);
        if($exists == 0){
            DB::table('cat_unit_types')->insert($update);
        }

        Dispatch::where('unit_type_id', 'TM')->update(['unit_type_id' => 'TNE']);

        $exists = DB::table('cat_unit_types')->where('id', 'TM')->count();
        if($exists){
            DB::table('cat_unit_types')->where('id', 'TM')->delete();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $exists = DB::table('cat_unit_types')->where('id', 'TNE')->count();
        if($exists>1){
            Log::info('Hay más de un registro con el id TNE'.$exists);
            $take = $exists-1;
            $to_delete = DB::table('cat_unit_types')->where('id', 'TNE')->take($take)->get();
            foreach ($to_delete as $row) {
                DB::table('cat_unit_types')->where('id', $row->id)->delete();
            }
        }
        
        $data = ['id' => 'TNE', 'description' => 'Toneladas', 'symbol' => 'TNL', 'active' => 1, 'show_symbol' => 1];
        $exists = DB::table('cat_unit_types')->where('id', $data['id'])->count();
        if($exists == 0){
            DB::table('cat_unit_types')->insert($data);
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
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

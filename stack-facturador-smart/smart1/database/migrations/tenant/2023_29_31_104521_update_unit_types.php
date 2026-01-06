<?php

use App\Models\Tenant\Dispatch;
use App\Models\Tenant\DispatchItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateUnitTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $new = ['id' => 'TNE', 'description' => 'Toneladas', 'symbol' => 'TNL', 'active' => 1, 'show_symbol' => 1];
        //buscar si ya existe new en la tabla
        $exists = DB::table('cat_unit_types')->where('id', $new['id'])->first();
        if(!$exists){
            DB::table('cat_unit_types')->insert($new);
        }
        $data = [
            ['search'=>'ZZ',  'id' => 'ZZ', 'description' => 'Servicio', 'symbol' => 'SERV', 'active' => 1, 'show_symbol' => 1],
            ['search'=>'BX',  'id' => 'BX', 'description' => 'Caja', 'symbol' => 'CAJ', 'active' => 1, 'show_symbol' => 1],
            ['search'=>'GLL',  'id' => 'GLL', 'description' => 'Galon', 'symbol' => 'GL', 'active' => 1, 'show_symbol' => 1],
            ['search'=>'GRM',  'id' => 'GRM', 'description' => 'Gramos', 'symbol' => 'GR', 'active' => 1, 'show_symbol' => 1],
            ['search'=>'KGM',  'id' => 'KGM', 'description' => 'Kilos', 'symbol' => 'KL', 'active' => 1, 'show_symbol' => 1],
            ['search'=>'LTR',  'id' => 'LTR', 'description' => 'Litro', 'symbol' => 'LT', 'active' => 1, 'show_symbol' => 1],
            ['search'=>'MTR',  'id' => 'MTR', 'description' => 'Metro', 'symbol' => 'M', 'active' => 1, 'show_symbol' => 1],
            ['search'=>'FOT',  'id' => 'FOT', 'description' => 'Pies', 'symbol' => 'PIE', 'active' => 1, 'show_symbol' => 1],
            ['search'=>'INH',  'id' => 'INH', 'description' => 'Pulgadas', 'symbol' => 'INCH', 'active' => 1, 'show_symbol' => 1],
            ['search'=>'NIU',  'id' => 'NIU', 'description' => 'Unidad', 'symbol' => 'UND', 'active' => 1, 'show_symbol' => 1],
            ['search'=>'YRD',  'id' => 'YRD', 'description' => 'Yarda', 'symbol' => 'YD', 'active' => 1, 'show_symbol' => 1],
            ['search'=>'HUR',  'id' => 'HUR', 'description' => 'Hora', 'symbol' => 'HR', 'active' => 1, 'show_symbol' => 1],
            ['search'=>'TM',  'id' => 'TM', 'description' => 'Toneladas', 'symbol' => 'TNL', 'active' => 1, 'show_symbol' => 1],
            ['search'=>'DZN',  'id' => 'DZN', 'description' => 'Docena', 'symbol' => 'DOC', 'active' => 1, 'show_symbol' => 1],
            ['search'=>'QD',  'id' => 'QD', 'description' => 'Cuarto de docena', 'symbol' => '1/4 DOC', 'active' => 1, 'show_symbol' => 1],
            ['search'=>'PK',  'id' => 'PK', 'description' => 'Paquete', 'symbol' => 'PQT', 'active' => 1, 'show_symbol' => 1],
            ['search'=>'MTQ',  'id' => 'MTQ', 'description' => 'Metro cúbico', 'symbol' => 'M3', 'active' => 1, 'show_symbol' => 1],
        ];

        foreach ($data as $row) {
            $to_insert = $row;
            //delete search field
            unset($to_insert['search']);
            DB::table('cat_unit_types')->where('id', $row['search'])->update($to_insert);
        }

        Dispatch::where('unit_type_id', 'TM')->update(['unit_type_id' => 'TNE']);

        DB::table('cat_unit_types')->where('id', 'TM')->delete();
        
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

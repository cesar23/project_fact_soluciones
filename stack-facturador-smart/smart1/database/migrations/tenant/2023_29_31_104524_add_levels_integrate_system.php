<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddLevelsIntegrateSystem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        $ids_2 = DB::connection('tenant')->table('module_levels')
            ->whereIn('value', ['list_document', 'items', 'clients'])
            ->pluck('id');

        $to_insert = [];
        foreach ($ids_2 as $id) {
            $to_insert[] = [
                'integrate_user_type_id' => 2,
                'module_level_id' => $id,
            ];
        }

        DB::connection('tenant')->table('integrate_user_levels_module')
            ->insert(
                $to_insert
            );
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

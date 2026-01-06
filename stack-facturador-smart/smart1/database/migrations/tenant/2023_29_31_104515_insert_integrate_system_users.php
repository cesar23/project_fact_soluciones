<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class InsertIntegrateSystemUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::connection('tenant')->table('integrate_user_type')
            ->insert([
                [
                    'id' => 1,
                    'name' => 'Administración',
                ],
                [
                    'id' => 2,
                    'name' => 'Comercial',
                ],
                [
                    'id' => 3,
                    'name' => 'Producción',
                ],
                [
                    'id' => 4,
                    'name' => 'Logistica',
                ]
            ]);
        $ids_2 = DB::connection('tenant')->table('module_levels')
            ->whereIn('value', ['quotations', 'sale_notes'])
            ->pluck('id');
        $ids_3 = DB::connection('tenant')->table('module_levels')
            ->whereIn('value', ['production_orders'])
            ->pluck('id');
        $ids_4 = DB::connection('tenant')->table('module_levels')
            ->whereIn('value', ['dispatch_orders','advanced_dispatches'])
            ->pluck('id');
        $to_insert = [];
        foreach ($ids_2 as $id) {
            $to_insert[] = [
                'integrate_user_type_id' => 2,
                'module_level_id' => $id,
            ];
        }
        foreach ($ids_3 as $id) {
            $to_insert[] = [
                'integrate_user_type_id' => 3,
                'module_level_id' => $id,
            ];
        }
        foreach ($ids_4 as $id) {
            $to_insert[] = [
                'integrate_user_type_id' => 4,
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

        // Eliminar registros de la tabla 'integrate_user_levels_module'
        DB::connection('tenant')->table('integrate_user_levels_module')->delete();

        // Truncar la tabla 'integrate_user_type'
        DB::connection('tenant')->table('integrate_user_type')->delete();
    }
}

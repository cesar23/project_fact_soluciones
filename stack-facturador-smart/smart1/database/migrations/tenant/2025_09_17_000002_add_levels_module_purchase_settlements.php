<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
//2025_09_17_000001_add_levels_module_purchase_settlements
class AddLevelsModulePurchaseSettlements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('tenant')->transaction(function () {
            $firstRecordId = DB::connection('tenant')->table('module_levels')->insertGetId([
                'value' => 'purchase_settlements',
                'description' => 'Liquidaciones de compra',
                'module_id' => 2,
            ]);


            // Obtener todos los ids de la tabla "users"
            $users = DB::connection('tenant')->table('users')->select('id')->get();

            // Iterar y por cada id crear dos registros en la tabla "module_level_user"
            foreach ($users as $user) {
                DB::connection('tenant')->table('module_level_user')->insert([
                    'module_level_id' => $firstRecordId,
                    'user_id' => $user->id,
                ]);

           
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection('tenant')->transaction(function () {
            // Obtener los ids de module_levels de los registros que tengan como value "summaries" y "voided"
            $moduleLevels = DB::connection('tenant')->table('module_levels')
                ->select('id')
                ->whereIn('value', ['purchase_settlements'])
                ->get();

            // Obtener todos los ids de la tabla "users"
            $users = DB::connection('tenant')->table('users')->select('id')->get();

            // Iterar y por cada id eliminar los registros en la tabla "module_level_user"
            foreach ($users as $user) {
                $recordExists = DB::connection('tenant')->table('module_level_user')
                    ->whereIn('module_level_id', $moduleLevels->pluck('id'))
                    ->where('user_id', $user->id)
                    ->exists();

                if ($recordExists) {
                    DB::connection('tenant')->table('module_level_user')
                        ->whereIn('module_level_id', $moduleLevels->pluck('id'))
                        ->where('user_id', $user->id)
                        ->delete();
                }
            }

            // Eliminar registros en la tabla "module_levels"
            DB::connection('tenant')->table('module_levels')->whereIn('value', ['purchase_settlements'])->delete();
        });
    }
}

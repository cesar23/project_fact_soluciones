<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
//2025_06_26_204242_add_modules_and_levels_more3
class AddModulesAndLevelsMore3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('tenant')->transaction(function () {
            // Array de módulos a insertar
            $modules = [
                ['value' => 'order-delivery', 'description' => 'Ordenes de entrega', 'module_id' => 3],
            
            ];

            $moduleIds = [];
            foreach ($modules as $module) {
                $record = DB::connection('tenant')->table('module_levels')
                    ->where('value', $module['value'])
                    ->first();

                if (!$record) {
                    $moduleIds[] = DB::connection('tenant')->table('module_levels')->insertGetId($module);
                } else {
                    $moduleIds[] = $record->id;
                }
            }

            // Asignar permisos a usuarios
            $users = DB::connection('tenant')->table('users')->select('id')->get();
            foreach ($users as $user) {
                foreach ($moduleIds as $moduleId) {
                    DB::connection('tenant')->table('module_level_user')
                        ->insertOrIgnore([
                            'module_level_id' => $moduleId,
                            'user_id' => $user->id,
                        ]);
                }
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
            $modules = ['order-delivery'];
            
            // Obtener IDs de los módulos
            $moduleIds = DB::connection('tenant')->table('module_levels')
                ->whereIn('value', $modules)
                ->pluck('id');

            // Eliminar registros de module_level_user
            DB::connection('tenant')->table('module_level_user')
                ->whereIn('module_level_id', $moduleIds)
                ->delete();

            // Eliminar los módulos
            DB::connection('tenant')->table('module_levels')
                ->whereIn('value', $modules)
                ->delete();

        
        });
    }
}


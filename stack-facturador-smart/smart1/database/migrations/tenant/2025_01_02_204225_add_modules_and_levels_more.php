<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
//2025_01_02_204225_add_modules_and_levels_more
class AddModulesAndLevelsMore extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('tenant')->transaction(function () {
            $modules = [
                [
                    'value' => 'payroll',
                    'description' => 'Planilla', 
                    'order_menu' => 10,
                ],
                [
                    'value' => 'exchange_currency',
                    'description' => 'Tipo de Cambio',
                    'order_menu' => 10,
                ],
                [
                    'value' => 'payment_list', 
                    'description' => 'Mis Pagos',
                    'order_menu' => 10,
                ]
            ];

            foreach($modules as $module) {
                if (!DB::connection('tenant')->table('modules')->where('value', $module['value'])->exists()) {
                    DB::connection('tenant')->table('modules')->insert($module);
                }
            }

            $firstRecordId = DB::connection('tenant')->table('modules')->where('value', 'documents')->first()->id;
            $secondRecordId = DB::connection('tenant')->table('modules')->where('value', 'payroll')->first()->id;
            $thirdRecordId = DB::connection('tenant')->table('modules')->where('value', 'exchange_currency')->first()->id;
            $fourthRecordId = DB::connection('tenant')->table('modules')->where('value', 'payment_list')->first()->id;
            $firstRecordLevelId = DB::connection('tenant')->table('module_levels')
                ->where('value', 'documents_recurrence')
                ->first();

            if (!$firstRecordLevelId) {
                $firstRecordLevelId = DB::connection('tenant')->table('module_levels')->insertGetId(
                    [
                        'value' => 'documents_recurrence',
                        'description' => 'Documentos Recurrentes',
                        'module_id' => $firstRecordId,
                    ],
                );
            } else {
                $firstRecordLevelId = $firstRecordLevelId->id;
            }

            $users = DB::connection('tenant')->table('users')->select('id')->get();
            foreach ($users as $user) {
                $moduleIds = [$secondRecordId, $thirdRecordId, $fourthRecordId];
                foreach($moduleIds as $moduleId) {
                    if (!DB::connection('tenant')->table('module_user')
                        ->where('module_id', $moduleId)
                        ->where('user_id', $user->id)
                        ->exists()) {
                            
                        DB::connection('tenant')->table('module_user')->insert([
                            'module_id' => $moduleId,
                            'user_id' => $user->id,
                        ]);
                    }
                }

                if (!DB::connection('tenant')->table('module_level_user')
                    ->where('module_level_id', $firstRecordLevelId)
                    ->where('user_id', $user->id)
                    ->exists()) {

                    DB::connection('tenant')->table('module_level_user')->insert([
                        'module_level_id' => $firstRecordLevelId,
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
            $moduleIds = DB::connection('tenant')->table('modules')
                ->select('id')
                ->whereIn('value', ['payroll', 'exchange_currency', 'payment_list'])
                ->get();
            foreach ($moduleIds as $moduleId) {
                DB::connection('tenant')->table('module_user')->where('module_id', $moduleId->id)->delete();
            }
            $ids = DB::connection('tenant')->table('module_levels')->whereIn('value', ['documents_recurrence'])->pluck('id')->toArray();
            DB::connection('tenant')->table('module_level_user')->whereIn('module_level_id', $ids)->delete();
            DB::connection('tenant')->table('module_levels')->whereIn('value', ['documents_recurrence'])->delete();
            DB::connection('tenant')->table('modules')->whereIn('value', ['payroll', 'exchange_currency', 'payment_list'])->delete();
        });
    }
}

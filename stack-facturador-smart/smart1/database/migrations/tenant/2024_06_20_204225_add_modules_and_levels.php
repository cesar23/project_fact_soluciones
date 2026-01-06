<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddModulesAndLevels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('tenant')->transaction(function () {
            $record =   DB::connection('tenant')->table('modules')->insertGetId(
                [
                    'value' => 'reservation',
                    'description' => 'Reservación',
                    'order_menu' => 8,
                ],

            );
            $firstRecordId = DB::connection('tenant')->table('modules')->where('value', 'pos')->first()->id;
            $firstRecordLevelId =   DB::connection('tenant')->table('module_levels')->insertGetId(
                [
                    'value' => 'advances_customers',
                    'description' => 'Anticipos clientes',
                    'module_id' => $firstRecordId,
                ],
            );
            $secondRecordLevelId =   DB::connection('tenant')->table('module_levels')->insertGetId(
                [
                    'value' => 'advances_suppliers',
                    'description' => 'Anticipos proveedores',
                    'module_id' => $firstRecordId,
                ],
            );
            // $users = DB::connection('tenant')->table('users')->select('id')->get();

            //iterar y por cada id crear dos registros en la tabla "module_level_user"
            // foreach ($users as $user) {
            DB::connection('tenant')->table('module_user')->insert(
                [
                    [
                        'module_id' => $firstRecordId,
                        'user_id' => 1,
                    ],

                ]
            );

            DB::connection('tenant')->table('module_level_user')->insert(
                [
                    [
                        'module_level_id' => $firstRecordLevelId,
                        'user_id' => 1,
                    ],
                    [
                        'module_level_id' => $secondRecordLevelId,
                        'user_id' => 1,
                    ],
                ]
            );
            // }
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
            $moduleId = DB::connection('tenant')->table('modules')
                ->select('id')
                ->where('value', 'reservation')
                ->first()->id;

            DB::connection('tenant')->table('module_user')->where('module_id', $moduleId)->delete();
            $ids = DB::connection('tenant')->table('module_levels')->whereIn('value', ['advances_customers', 'advances_suppliers'])->pluck('id')->toArray();
            DB::connection('tenant')->table('module_level_user')->whereIn('module_level_id', $ids)->delete();
            DB::connection('tenant')->table('module_levels')->whereIn('value', ['advances_customers', 'advances_suppliers'])->delete();
            DB::connection('tenant')->table('modules')->where('value', 'reservation')->delete();
        });
    }
}

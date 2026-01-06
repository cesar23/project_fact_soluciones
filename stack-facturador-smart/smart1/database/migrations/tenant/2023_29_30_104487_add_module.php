<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddModule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('tenant')->transaction(function () {
            $firstRecordId =   DB::connection('tenant')->table('modules')->insertGetId(
                [
                    'value' => 'whatsapp',
                    'description' => 'Whatsapp',
                    'order_menu' => 1,
                ],

            );
            $secondRecordId =   DB::connection('tenant')->table('modules')->insertGetId(
                [
                    'value' => 'suppliers',
                    'description' => 'Proveedores',
                    'order_menu' => 6,
                ],

            );
            //obtener todos los ids de la tabla "users"
            $users = DB::connection('tenant')->table('users')->select('id')->get();

            //iterar y por cada id crear dos registros en la tabla "module_level_user"
            foreach ($users as $user) {
                DB::connection('tenant')->table('module_user')->insert(
                    [
                        [
                            'module_id' => $firstRecordId,
                            'user_id' => $user->id,
                        ],
                        [
                            'module_id' => $secondRecordId,
                            'user_id' => $user->id,
                        ],
                    ]
                );
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
            $moduleId = DB::connection('tenant')->table('modules')
                ->select('id')
                ->where('value', 'whatsapp')
                ->first()->id;
            $moduleSupplierId = DB::connection('tenant')->table('modules')
                ->select('id')
                ->where('value', 'suppliers')
                ->first()->id;

            DB::connection('tenant')->table('module_user')
                ->whereIN('module_id', [$moduleId, $moduleSupplierId])
                ->delete();

            DB::connection('tenant')->table('modules')
                ->whereIn('value', ['whatsapp', 'suppliers'])
                ->delete();
        });
    }
}

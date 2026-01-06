<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddModulesReservationsAdvances extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('modules')->insert(
            [
                [
                    'value' => 'reservation',
                    'description' => 'Reservación',
                    'sort' => 8,
                ],
            ]
        );


        $module_id = DB::table('modules')->where('value', 'pos')->first()->id;

        DB::table('module_levels')->insert(
            [
                [
                    'value' => 'advances_customers',
                    'description' => 'Anticipos clientes',
                    'module_id' => $module_id,
                ],
                [
                    'value' => 'advances_suppliers',
                    'description' => 'Anticipos proveedores',
                    'module_id' => $module_id,
                ],
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $module_id = DB::table('modules')->where('value', 'pos')->first()->id;

        DB::table('module_levels')->where('module_id', $module_id)
            ->whereIn('value', ['advances_customers', 'advances_suppliers'])
            ->delete();
        DB::table('modules')->where('value', 'reservation')->delete();
    }
}

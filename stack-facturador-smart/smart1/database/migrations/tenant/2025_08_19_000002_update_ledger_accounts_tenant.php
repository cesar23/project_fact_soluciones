<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

//2025_08_19_000002_update_ledger_accounts_tenant
class UpdateLedgerAccountsTenant extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = DB::connection('tenant')->table('ledger_accounts_tenant');
        $toInsert = [
            [
                'code' => '87',
                'name' => 'PARTICIPACIONES DE LOS TRABAJADORES',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '871',
                'name' => 'PARTICIPACION DE LOS TRABAJADORES - CORRIENTE',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '8711',
                'name' => 'PARTICIPACION DE LOS TRABAJADORES - CORRIENTE',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '871101',
                'name' => 'PARTICIPACION DE LOS TRABAJADORES - CORRIENTE',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'code' => '529',
                'name' => 'CAPITAL ADICIONAL POR ABSORCIÓN DE PÉRDIDAS',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '5291',
                'name' => 'CAPITAL ADICIONAL POR ABSORCIÓN DE PÉRDIDAS',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '529101',
                'name' => 'CAPITAL ADICIONAL POR ABSORCIÓN DE PÉRDIDAS',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'code' => '591102',
                'name' => 'UTILIDADES ACUMULADAS DEL EJERCICIO',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => '592102',
                'name' => 'PERDIDAS ACUMULADAS DEL EJERCICIO',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];


        $table->where('code', '591101')->update([
            'name' => 'UTILIDADES ACUMULADAS AÑOS ANTERIORES',
        ]);
        $table->where('code', '592101')->update([
            'name' => 'PERDIDAS ACUMULADAS AÑOS ANTERIORES',
        ]);
        $table->insert($toInsert);
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204342_set_info_to_technical_services_states
class SetInfoToTechnicalServicesStates extends Migration
{
    public function up()
    {
        $tenant_connection = DB::connection('tenant');
        $tenant_connection->table('technical_services_states')->insert([
            [
                'name' => 'Registrado',
            ],
            [
                'name' => 'Aceptado',
            ],
            [
                'name' => 'Rechazado',
            ],
        ]);
    }

    public function down()
    {
        $tenant_connection = DB::connection('tenant');
        $tenant_connection->table('technical_services_states')->whereIn('name', ['Registrado', 'Aceptado', 'Rechazado'])->delete();
    }
}

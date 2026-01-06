<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204336_insert_states_delivery
class InsertStatesDelivery extends Migration
{
    public function up()
    {
        DB::connection('tenant')->table('state_deliveries')->insert([
            ['id' => 1, 'name' => 'Pendiente', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Entregado', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Devolución', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'Garantía', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'name' => 'Incidencia', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        DB::connection('tenant')->table('state_deliveries')->where('id', 1)->delete();
    }
}

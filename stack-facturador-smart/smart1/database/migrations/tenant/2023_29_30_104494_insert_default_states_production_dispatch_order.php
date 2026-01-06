<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class InsertDefaultStatesProductionDispatchOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('tenant')->table('state_production_orders')->insert([
            [ 'description' => 'Pendiente', 'active' => true],
            [ 'description' => 'Picking', 'active' => true],
            [ 'description' => 'Realizado', 'active' => true],
            [ 'description' => 'Recojo | Almacén', 'active' => true],
            [ 'description' => 'Entregado | Almacén', 'active' => true],
        ]);

        DB::connection('tenant')->table('state_dispatch_orders')->insert([
            [ 'description' => 'Pendiente', 'active' => true],
            [ 'description' => 'En Ruta - Lima', 'active' => true],
            [ 'description' => 'Entregado - Lima', 'active' => true],
            [ 'description' => 'Entregado - Provincia', 'active' => true],
            [ 'description' => 'No entregado', 'active' => true],
            [ 'description' => 'Programado', 'active' => true],
        ]);
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

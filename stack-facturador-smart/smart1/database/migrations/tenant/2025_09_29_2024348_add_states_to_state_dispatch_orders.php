<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
//2025_09_29_2024348_add_states_to_state_dispatch_orders.php
class AddStatesToStateDispatchOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('tenant')
            ->table('state_dispatch_orders')
            ->insert([
                ['description' => 'Recojo | Almacén','active' => true],
                ['description' => 'Entregado | Almacén','active' => true],
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection('tenant')
            ->table('state_dispatch_orders')
            ->where('description', 'Recojo | Almacén')
            ->delete();

        DB::connection('tenant')
            ->table('state_dispatch_orders')
            ->where('description', 'Entregado | Almacén')
            ->delete();
    }
}

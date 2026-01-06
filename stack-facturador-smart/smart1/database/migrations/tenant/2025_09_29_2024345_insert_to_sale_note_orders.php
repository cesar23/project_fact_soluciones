<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InsertToSaleNoteOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('tenant')->table('state_sale_note_orders')->insert([
            ['description' => 'Lima – Envió a domicilio'],
            ['description' => 'Lima – Recojo en Almacén'],
            ['description' => 'Provincia – Envió a domicilio'],
            ['description' => 'Provincia – Recojo en Agencia'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection('tenant')->table('state_sale_note_orders')->delete();
    }
}

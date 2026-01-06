<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_08_21_000020_set_data_to_nulleable_qpos
class SetDataToNulleableQpos extends Migration         
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Actualizar registros con created_at nulo en la tabla persons
        DB::connection('tenant')->table('persons')
            ->whereNull('created_at')
            ->update(['created_at' => now()]);

        // Actualizar registros con description nulo en la tabla items
        DB::connection('tenant')->table('items')
            ->whereNull('description')
            ->update(['description' => 'SIN DESCRIPCION']);

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

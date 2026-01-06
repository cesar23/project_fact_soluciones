<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_09_08_083002_set_amount_plastic_update_2025
class SetAmountPlasticUpdate2025 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Verificar si la tabla configurations existe y actualizar el valor por defecto
        if (Schema::hasTable('configurations')) {
            DB::connection('tenant')->table('configurations')->update([
                'amount_plastic_bag_taxes' => 0.5
            ]);
        }
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_08_19_000005_create_series_to_purchase_orders
class CreateSeriesToPurchaseOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::connection('tenant')->table('cat_document_types')->insert([
            [
                'id' => 'OCB',
                'short' => 'OCB',
                'description' => 'Orden de Compra Bienes',
                'active' => true,
            ],
            
            [
                'id' => 'OCS',
                'short' => 'OCS',
                'description' => 'Orden de Compra Servicios',
                'active' => true,
            ],
        ]);
        // Verificar si existe el establecimiento 1 antes de insertar las series
        $establishment = DB::connection('tenant')->table('establishments')->where('id', 1)->first();
        
        if ($establishment) {
            DB::connection('tenant')->table('series')->insert([
                [
                    'establishment_id' => 1,
                    'document_type_id' => 'OCB',
                    'number' => 'OCB1',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'establishment_id' => 1,
                    'document_type_id' => 'OCS',
                    'number' => 'OCS1',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
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
        DB::connection('tenant')->table('series')->where('document_type_id', 'OCB')->delete();
        DB::connection('tenant')->table('series')->where('document_type_id', 'OCS')->delete();
    }
}

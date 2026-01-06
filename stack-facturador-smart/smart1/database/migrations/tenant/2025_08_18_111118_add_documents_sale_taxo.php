<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

//2025_07_16_111118_add_documents_sale_taxo


class AddDocumentsSaleTaxo extends Migration
{
    /**
     * Run the migrations.




     *
     * @return void
     */

    public function up()
    {

        $table = DB::connection('tenant')->table('app_configuration_taxo');
        $now = now();
        $toInsert = [
            [
                'menu' => 'Ventas Facturas',
                'route' => 'sale-invoice',
                'is_visible' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'menu' => 'Ventas Boletas',
                'route' => 'sale-bill',
                'is_visible' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'menu' => 'Ventas Notas de Venta',
                'route' => 'sale-note',
                'is_visible' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'menu' => 'Ventas Cotizaciones',
                'route' => 'sale-quotation',
                'is_visible' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'menu' => 'Ventas Pedidos',
                'route' => 'sale-order-note',
                'is_visible' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
        ];

        $table->insert($toInsert);
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection('tenant')->table('app_configuration_taxo')->whereIn('route', ['sale-invoice', 'sale-bill', 'sale-note', 'sale-quotation', 'sale-order-note'])->delete();
    }
}

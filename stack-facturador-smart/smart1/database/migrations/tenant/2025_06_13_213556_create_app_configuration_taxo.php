<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

//2025_06_13_213556_create_app_configuration_taxo
class CreateAppConfigurationTaxo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_configuration_taxo', function (Blueprint $table) {
            $table->id();
            $table->string('menu');
            $table->string('route')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

        });
        DB::connection('tenant')->table('app_configuration_taxo')->insert([
            ['menu' => 'Dashboard', 'route' => 'dashboard', 'is_visible' => true],
            ['menu' => 'Crear Comprobantes', 'route' => 'create_vouchers', 'is_visible' => true],
            // ['menu' => 'Información General', 'route' => 'general_information', 'is_visible' => true],
            // ['menu' => 'Impresión y Envío', 'route' => 'printing_and_shipping', 'is_visible' => true],
            ['menu' => 'Lista de Comprobantes', 'route' => 'vouchers_list', 'is_visible' => true],
            ['menu' => 'Lista de Productos', 'route' => 'products_list', 'is_visible' => true],
            ['menu' => 'Lista de Clientes', 'route' => 'customers_list', 'is_visible' => true],
            ['menu' => 'Guía Remitente', 'route' => 'sender_guide', 'is_visible' => true],
            ['menu' => 'Guía Transportista', 'route' => 'carrier_guide', 'is_visible' => true],
            ['menu' => 'Lista de Vehículos', 'route' => 'vehicles_list', 'is_visible' => true],
            ['menu' => 'Lista de Conductores', 'route' => 'drivers_list', 'is_visible' => true],
            ['menu' => 'Lista de Transportistas', 'route' => 'carriers_list', 'is_visible' => true],
            ['menu' => 'Lista de Categorías', 'route' => 'categories_list', 'is_visible' => true],
            ['menu' => 'Lista de Marcas', 'route' => 'brands_list', 'is_visible' => true],
            ['menu' => 'Inventario', 'route' => 'inventory', 'is_visible' => true],
            ['menu' => 'Anulación de Facturas', 'route' => 'invoice_cancellation', 'is_visible' => true],
            ['menu' => 'Compras', 'route' => 'purchases', 'is_visible' => true],
            ['menu' => 'Cuentas por Cobrar', 'route' => 'accounts_receivable', 'is_visible' => true],
            ['menu' => 'Caja', 'route' => 'cash', 'is_visible' => true],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('app_configuration_taxo');
    }
}

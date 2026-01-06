<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CustomInitRoutes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_init_routes', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->string('name');
            $table->string('route');
        });

        DB::connection('tenant')
        ->table('custom_init_routes')
        ->insert([
            [ 'description' => 'Dashboard', 'name' => 'dashboard', 'route' => '/dashboard'],
            [ 'description' => 'Documentos', 'name' => 'documents', 'route' => '/documents/create'],
            [ 'description' => 'Notas de venta', 'name' => 'sale_notes', 'route' => '/sale-notes/create'],
            [ 'description' => 'Cotizaciones', 'name' => 'quotations', 'route' => '/quotations/create'],
            [ 'description' => 'Pedidos', 'name' => 'order-notes', 'route' => '/order-notes/create'],
            [ 'description' => 'POS', 'name' => 'pos', 'route' => '/pos'],
            [ 'description' => 'Venta rápida', 'name' => 'pos-garage', 'route' => '/pos/garage'],
            [ 'description' => 'Compras', 'name' => 'purchases', 'route' => '/purchases/create'],
            [ 'description' => 'Guías de remisión', 'name' => 'dispatches', 'route' => '/dispatches/create'],
            [ 'description' => 'Guías de remisión - transportista', 'name' => 'dispatch_carrier', 'route' => '/dispatch_carrier/create'],
            [ 'description' => 'Hotel', 'name' => 'hotel', 'route' => '/hotels/reception'],
            [ 'description' => 'Suscripción escolar', 'name' => 'suscription', 'route' => '/client/childrens'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection('tenant')->table('custom_init_routes')->delete();
        Schema::dropIfExists('custom_init_routes');
    }
}

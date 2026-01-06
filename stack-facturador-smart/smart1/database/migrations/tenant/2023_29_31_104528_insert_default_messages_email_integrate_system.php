<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InsertDefaultMessagesEmailIntegrateSystem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('tenant')
            ->table('messages_email_integrate_system')
            ->insert([
                [
                    'trigger_event' => 'production_order.2',
                    'comment' => 'Picking',
                    'message' =>  'Su pedido se encuentra en producción.'
                ],
                [
                    'trigger_event' => 'production_order.4',
                    'comment' => 'Recojo en almacén',
                    'message' => 'Su pedido se encuentra listo para recojo en almacén.'
                ],
                [
                    'trigger_event' => 'production_order.5',
                    'comment' => 'Entregado en almacén',
                    'message' => 'Su pedido ha sido entregado.'
                ],
                [
                    'trigger_event' => 'dispatch_order.2',
                    'comment' => 'Ruta lima',
                    'message' => 'Su pedido se encuentra en ruta.'
                ],
                [
                    'trigger_event' => 'dispatch_order.3',
                    'comment' => 'Entregado lima',
                    'message' => 'Su pedido ha sido entregado.'
                ],
                [
                    'trigger_event' => 'dispatch_order.4',
                    'comment' => 'Entregado Provincia',
                    'message' => 'Su pedido ha sido entregado en la agencia de transportes en lima.'
                ],
                [
                    'trigger_event' => 'dispatch_order.5',
                    'comment' => 'No entregado',
                    'message' => 'Su pedido no ha podido ser entregado.'
                ],
                [
                    'trigger_event' => 'sale_note.02',
                    'comment' => 'Pago aprobado',
                    'message' => 'Su pago ha sido aprobado.'
                ],
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

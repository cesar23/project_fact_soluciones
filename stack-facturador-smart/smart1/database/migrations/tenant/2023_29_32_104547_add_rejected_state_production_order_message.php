<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddRejectedStateProductionOrderMessage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::connection('tenant')->table('messages_email_integrate_system')->insert([
               [
                'trigger_event' => 'production_order.6',
                'comment' => 'Orden rechazada',
                'message' => 'La orden de producción ha sido rechazada.'
               ]
            ]);
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection('tenant')->table('messages_email_integrate_system')->where('trigger_event', 'production_order.6')->delete();
    }
}

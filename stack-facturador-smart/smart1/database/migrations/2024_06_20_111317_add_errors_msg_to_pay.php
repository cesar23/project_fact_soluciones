<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddErrorsMsgToPay extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('errors', function (Blueprint $table) {
            $table->longText('msg_to_pay')->nullable();

        });
        DB::table('errors')->update(['msg_to_pay' => 'Su sistema está próximo a ser bloqueado debido a una deuda pendiente. Para evitar interrupciones en su servicio, por favor realice el pago correspondiente en día laborable y dentro del horario de oficina. Asegúrese de informar el pago a la brevedad para evitar el bloqueo del sistema.']);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        Schema::table('errors', function (Blueprint $table) {
            $table->dropColumn('msg_to_pay');
        });
    }
}

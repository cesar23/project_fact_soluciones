<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddStateRejectedDispatchOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $exist = DB::connection('tenant')->table('state_dispatch_orders')->where('description', 'Rechazado')->exists();
        if ($exist) {
            return;
        }
        DB::connection('tenant')->table('state_dispatch_orders')->insert([
            [ 'description' => 'Rechazado', 'active' => true],
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

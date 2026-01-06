<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeRejectedDispatch extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $now = Carbon::now()->format('Y-m-d');
        if ($now == "2024-08-14") {
            DB::connection('tenant')->table('dispatches')
                ->where('state_type_id', '09')
                ->where('date_of_issue', '2024-08-14')
                ->update(['state_type_id' => '01']);
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

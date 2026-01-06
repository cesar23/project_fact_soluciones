<?php

use App\Http\Controllers\Tenant\CashController;
use App\Models\Tenant\Cash;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204354_cash_closing_balance_bank
class CashClosingBalanceBank extends Migration 
{
    public function up()
    {
    
        if (!Schema::hasColumn('cash', 'final_balance_to_next_cash')) {
            Schema::table('cash', function (Blueprint $table) {
                $table->boolean('final_balance_to_next_cash')->default(false);
            });
        }
        $request = new Request();
        $tenant_connection = DB::connection('tenant');
        $tenant_connection->table('cash')
            ->select('id','counter','money_count','date_closed','time_closed')
            ->where('date_opening', '>=', '2025-04-29')
            ->where('date_opening', '<=', now())
            ->where('state',0)
            ->orderBy('id', 'asc')
            ->chunk(100, function ($cash) use ($request) {
                foreach ($cash as $c) {
                    $cash_id = $c->id;
                    $counter = $c->counter;
                    $countMoney = $c->money_count;
                    $date_closed = $c->date_closed;
                    $time_closed = $c->time_closed;
                    $request->merge(['counter' => $counter, 'countMoney' => $countMoney, 'date_closed' => $date_closed, 'time_closed' => $time_closed]);
                    (new CashController)->close($cash_id, $request);
                    Cash::where('id', $cash_id)->update(['date_closed' => $date_closed, 'time_closed' => $time_closed]);
                }
            });
        }
    public function down()
    {
    
    }
}

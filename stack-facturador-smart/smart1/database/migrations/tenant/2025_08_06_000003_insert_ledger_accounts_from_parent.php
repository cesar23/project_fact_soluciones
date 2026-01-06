<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_08_06_000003_insert_ledger_accounts_from_parent
class InsertLedgerAccountsFromParent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('system')->table('ledger_accounts')->orderBy('code', 'asc')->chunk(1000, function ($ledgerAccounts) {
            $data = [];
            foreach ($ledgerAccounts as $ledgerAccount) {
                $data[] = [
                    'code' => $ledgerAccount->code,
                    'name' => $ledgerAccount->name,
                    'active' => true,
                ];
            }
            
            DB::table('ledger_accounts_tenant')->insert($data);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('ledger_accounts_tenant')->truncate();
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204349_cash_final_balance_with_banks
class CashFinalBalanceWithBanks extends Migration
{
    public function up()
    {
        
        if (!Schema::hasColumn('cash', 'final_balance_with_banks')) {
            Schema::table('cash', function (Blueprint $table) {
                $table->decimal('final_balance_with_banks', 12, 2)->after('final_balance')->default(0);
            });
        }
    }

    public function down()
    {
        Schema::table('cash', function (Blueprint $table) {
            $table->dropColumn('final_balance_with_banks');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_06_26_204257_cash_add_final_balance_to_next_cash

class CashAddFinalBalanceToNextCash extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('cash', 'final_balance_to_next_cash')) {
            Schema::table('cash', function (Blueprint $table) {
                $table->boolean('final_balance_to_next_cash')->default(false);
            });
        }

    
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cash', function (Blueprint $table) {
            $table->dropColumn('final_balance_to_next_cash');
        });

        
    }
}

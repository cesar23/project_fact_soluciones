<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_06_26_204250_add_cash_by_establishment_to_config
class AddCashByEstablishmentToConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('configurations', 'cash_by_establishment')) {
            Schema::table('configurations', function (Blueprint $table) {
                $table->boolean('cash_by_establishment')->default(false);
            });
        }

        if (!Schema::hasColumn('configurations', 'automatic_cash_beginning_balance')) {
            Schema::table('configurations', function (Blueprint $table) {
                $table->boolean('automatic_cash_beginning_balance')->default(false);
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
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('cash_by_establishment');
            $table->dropColumn('automatic_cash_beginning_balance');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ConfigurationPosFavoriteCounterCreditBottles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->boolean('pos_items_favorite')->default(false);
            $table->boolean('pos_cash_counter')->default(false);
            $table->boolean('pos_credit')->default(false);
            $table->boolean('pos_bottles')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('pos_items_favorite');
            $table->dropColumn('pos_cash_counter');
            $table->dropColumn('pos_credit');
            $table->dropColumn('pos_bottles');
        });
        
    }
}

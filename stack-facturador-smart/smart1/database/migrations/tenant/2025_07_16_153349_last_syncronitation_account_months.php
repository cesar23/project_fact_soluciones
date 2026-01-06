<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

//2025_07_16_153349_last_syncronitation_account_months
class LastSyncronitationAccountMonths extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_months', function (Blueprint $table) {
            $table->dateTime('last_syncronitation')->nullable()->after('total_credit');
        });
    
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('account_months', function (Blueprint $table) {
            $table->dropColumn('last_syncronitation');
        });
    }
}

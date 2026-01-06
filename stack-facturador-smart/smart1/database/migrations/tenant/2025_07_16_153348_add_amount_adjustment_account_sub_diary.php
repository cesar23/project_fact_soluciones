<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

//2025_07_16_153348_add_amount_adjustment_account_sub_diary
class AddAmountAdjustmentAccountSubDiary extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_sub_diaries', function (Blueprint $table) {
            $table->decimal('amount_adjustment', 12, 2)->default(0)->after('is_manual');
        });
        Schema::table('account_sub_diary_items', function (Blueprint $table) {
            $table->decimal('amount_adjustment', 12, 2)->default(0)->after('credit_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('account_sub_diaries', function (Blueprint $table) {
            $table->dropColumn('amount_adjustment');
        });
        Schema::table('account_sub_diary_items', function (Blueprint $table) {
            $table->dropColumn('amount_adjustment');
        });
    }
}

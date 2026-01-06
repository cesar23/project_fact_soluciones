<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204348_add_account_month_to_sub_diary
class AddAccountMonthToSubDiary extends Migration
{
    public function up()
    {
        Schema::table('account_sub_diaries', function (Blueprint $table) {
            $table->foreignId('account_month_id')->constrained('account_months')->after('complete');
        });
    }

    public function down()
    {
        Schema::table('account_sub_diaries', function (Blueprint $table) {
            $table->dropColumn('account_month_id');
        });
    }
}

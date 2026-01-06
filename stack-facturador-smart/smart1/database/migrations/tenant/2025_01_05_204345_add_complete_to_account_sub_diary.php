<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204345_add_complete_to_account_sub_diary
class AddCompleteToAccountSubDiary extends Migration
{
    public function up()
    {
        Schema::table('account_sub_diaries', function (Blueprint $table) {
            $table->boolean('complete')->default(true)->after('book_code');
        });
    }

    public function down()
    {
        Schema::table('account_sub_diaries', function (Blueprint $table) {
            $table->dropColumn('complete');
        });
    }
}

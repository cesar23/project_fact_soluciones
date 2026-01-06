<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204350_add_to_sub_diaries_is_manual_property
class AddToSubDiariesIsManualProperty extends Migration
{
    public function up()
    {
        Schema::table('account_sub_diaries', function (Blueprint $table) {
            $table->boolean('is_manual')->default(false)->after('complete');
        });
    }

    public function down()
    {
        Schema::table('account_sub_diaries', function (Blueprint $table) {
            $table->dropColumn('is_manual');
        });
    }
}

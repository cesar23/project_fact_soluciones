<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

//2025_07_16_153346_add_correlative_number_subdiary
class AddCorrelativeNumberSubdiary extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('account_sub_diaries', function (Blueprint $table) {
            $table->string('correlative_number')->nullable()->after('id');
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
            $table->dropColumn('correlative_number');
        });
    }
}

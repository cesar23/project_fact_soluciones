<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2024_08_18_011164_add_trade_name_pdf


class AddTradeNamePdf extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('configurations', 'trade_name_pdf')) {
            Schema::table('configurations', function (Blueprint $table) {
                $table->boolean('trade_name_pdf')->default(false);
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
        if (Schema::hasColumn('configurations', 'trade_name_pdf')) {
            Schema::table('configurations', function (Blueprint $table) {
                $table->dropColumn('trade_name_pdf');
            });
        }
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCashReportHidden extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->boolean('cash_report_hidden')->default(false);
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
            $table->dropColumn('cash_report_hidden');
        });
    }
}

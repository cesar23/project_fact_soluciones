<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204354_add_columna_with_igv_product_report
class AddColumnaWithIgvProductReport extends Migration 
{
    public function up()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->tinyInteger('with_igv_product_report_cash')->nullable();

            
        });
    }

    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('with_igv_product_report_cash');
        });
    }
}

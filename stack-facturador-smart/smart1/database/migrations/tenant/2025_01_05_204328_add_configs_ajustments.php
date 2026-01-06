<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204328_add_configs_ajustments
class AddConfigsAjustments extends Migration
{
    public function up()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->boolean('update_unit_price_sale_in_purchase')->default(false);
            $table->boolean('hide_quotations_payment')->default(false);
            $table->boolean('cash_report_with_banks')->default(true);


        });
        
    }

    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('update_unit_price_sale_in_purchase');
            $table->dropColumn('hide_quotations_payment');
            $table->dropColumn('cash_report_with_banks');
        });
    }
}

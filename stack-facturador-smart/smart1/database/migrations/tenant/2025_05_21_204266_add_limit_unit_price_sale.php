<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


//2025_05_21_204266_add_limit_unit_price_sale
class AddLimitUnitPriceSale extends Migration
{
    public function up()
    {
    

        if (!Schema::hasColumn('configurations', 'limit_unit_price_sale')) {
            Schema::table('configurations', function (Blueprint $table) {
                $table->boolean('limit_unit_price_sale')->default(false);
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('configurations', 'limit_unit_price_sale')) {
            Schema::table('configurations', function (Blueprint $table) {
                $table->dropColumn('limit_unit_price_sale');
            });
        }
    }
}
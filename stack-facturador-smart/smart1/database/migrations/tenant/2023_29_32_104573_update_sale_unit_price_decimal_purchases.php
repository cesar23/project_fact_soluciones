<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSaleUnitPriceDecimalPurchases extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('unit_value', 20, 10)->change();
            $table->decimal('unit_price', 20, 10)->change();
        });

    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('unit_price', 16, 6)->change();
            $table->decimal('unit_value', 16, 6)->change();
        });
        
    }
}

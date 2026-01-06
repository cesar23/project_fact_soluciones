<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSaleUnitPriceDecimal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('sale_unit_price', 20, 10)->change();
            $table->decimal('purchase_unit_price', 20, 10)->change();
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
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('sale_unit_price', 16, 6)->change();
            $table->decimal('purchase_unit_price', 16, 6)->change();
        });
        
    }
}

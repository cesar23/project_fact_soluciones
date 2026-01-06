<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddAmountPlasticBagTaxesToItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumn('items', 'amount_plastic_bag_taxes')) {
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('amount_plastic_bag_taxes', 6, 2)->default(0.5)->after('has_isc');
            //
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
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('amount_plastic_bag_taxes');
            //
        });
    }
}

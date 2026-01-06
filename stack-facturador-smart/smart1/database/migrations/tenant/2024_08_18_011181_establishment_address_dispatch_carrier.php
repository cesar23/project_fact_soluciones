<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 2024_08_18_011181_establishment_address_dispatch_carrier


class EstablishmentAddressDispatchCarrier extends Migration

{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('configurations', 'establishment_address_dispatch_carrier')) {
            Schema::table('configurations', function (Blueprint $table) {
                $table->boolean('establishment_address_dispatch_carrier')->default(false);
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
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('establishment_address_dispatch_carrier');
        });
    }
}

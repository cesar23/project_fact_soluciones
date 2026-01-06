<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//2025_09_11_120001_add_date_of_outage_to_supply_outage
class AddDateOfOutageToSupplyOutage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('supply_outage', function (Blueprint $table) {
            $table->date('date_of_outage')->nullable()->after('observation');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('supply_outage', function (Blueprint $table) {
            $table->dropColumn('date_of_outage');
        });
    }
}
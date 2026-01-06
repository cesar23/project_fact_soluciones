<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddColumnsToTechnicalServicesNew extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('technical_services', function (Blueprint $table) {
            $table->boolean('preventive_maintenance')->default(false);
            $table->boolean('corrective_maintenance')->default(false);
            $table->boolean('ironing_and_painting')->default(false);
            $table->boolean('equipments')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('technical_services', function (Blueprint $table) {
            $table->dropColumn(['preventive_maintenance','corrective_maintenance','ironing_and_painting','equipments']);
        });
    }
}

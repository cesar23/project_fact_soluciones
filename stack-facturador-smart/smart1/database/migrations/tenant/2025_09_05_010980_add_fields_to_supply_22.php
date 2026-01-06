<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_09_05_010971_add_fields_to_supply_22
class AddFieldsToSupply22 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('supplies', function (Blueprint $table) {
            $table->unsignedInteger('supply_via_id')->nullable();
            $table->unsignedInteger('zone_id')->nullable()->change();
            $table->unsignedInteger('state_supply_id')->nullable()->change();
            $table->boolean('active')->default(true);
            $table->text('observation')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('supplies', function (Blueprint $table) {
            $table->dropColumn(['supply_via_id']);
            $table->dropColumn(['active']);
            $table->dropColumn(['observation']);
        });
        Schema::table('supplies', function (Blueprint $table) {
            $table->unsignedInteger('zone_id')->change();
            $table->unsignedInteger('state_supply_id')->change();
        });
    }

    
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_09_05_010971_add_fields_to_supply
class AddFieldsToSupply extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('supplies', function (Blueprint $table) {
            $table->string('old_code')->after('code')->nullable();
            $table->string('cod_route')->nullable()->after('old_code');
            $table->string('zone_type')->nullable();
            $table->string('mz')->nullable();
            $table->string('lte')->nullable();
            $table->string('und')->nullable();
            $table->string('number')->nullable();
            $table->boolean('meter')->default(false);
            $table->string('meter_code')->nullable();
            $table->boolean('sewerage')->default(false);
    
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
            $table->dropColumn(['old_code', 'cod_route', 'zone_type', 'mz', 'lte', 'und', 'number', 'meter', 'meter_code', 'sewerage']);
        });
    }
}

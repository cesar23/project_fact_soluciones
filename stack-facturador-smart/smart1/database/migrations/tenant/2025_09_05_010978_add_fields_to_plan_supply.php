<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_09_05_010978_add_fields_to_plan_supply
class AddFieldsToPlanSupply extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('supplie_plans', function (Blueprint $table) {
            $table->string('type_zone')->nullable();
            $table->string('type_plan')->nullable();
            $table->decimal('price_c_m', 10, 2)->default(0);
            $table->decimal('price_s_m', 10, 2)->default(0);
            $table->decimal('price_alc', 10, 2)->default(0);
            $table->string('observation')->nullable();
    
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('supplie_plans', function (Blueprint $table) {
            $table->dropColumn(['type_zone', 'type_plan', 'price_c_m', 'price_s_m', 'price_alc', 'observation']);
        });
    }
}

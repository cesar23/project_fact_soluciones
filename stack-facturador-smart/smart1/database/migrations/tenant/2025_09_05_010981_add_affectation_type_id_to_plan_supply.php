<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_09_05_010981_add_affectation_type_id_to_plan_supply
class AddAffectationTypeIdToPlanSupply extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('supplie_plans', function (Blueprint $table) {
            $table->string('affectation_type_id')->default('20');
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
            $table->dropColumn(['affectation_type_id']);
        });
    }

    
}

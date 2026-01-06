<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//2025_09_29_2024350_make_nulleable_supply_concept_id_in_supply_solicitude_items
class MakeNulleableSupplyConceptIdInSupplySolicitudeItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('supply_solicitude_items', function (Blueprint $table) {
            $table->unsignedInteger('supply_concept_id')->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('supply_solicitude_items', function (Blueprint $table) {
        });
    }
}

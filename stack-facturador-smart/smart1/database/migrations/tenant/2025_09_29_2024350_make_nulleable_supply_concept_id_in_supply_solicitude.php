<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class MakeNulleableSupplyConceptIdInSupplySolicitude extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('supply_solicitude', function (Blueprint $table) {
            $table->unsignedInteger('supply_concept_id')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('supply_solicitude', function (Blueprint $table) {
        });
    }
}

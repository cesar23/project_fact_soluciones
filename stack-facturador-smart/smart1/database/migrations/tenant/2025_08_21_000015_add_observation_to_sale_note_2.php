<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//2025_08_21_000015_add_obsertation_to_sale_note_2
class AddObservationToSaleNote2 extends Migration      
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('sale_notes', 'observation')) {
            Schema::table('sale_notes', function (Blueprint $table) {
                $table->string('observation', 500)->nullable()->after('plate_number');

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
        if (Schema::hasColumn('sale_notes', 'observation')) {   
            Schema::table('sale_notes', function (Blueprint $table) {
                $table->dropColumn('observation');
            });
        }
    }
}

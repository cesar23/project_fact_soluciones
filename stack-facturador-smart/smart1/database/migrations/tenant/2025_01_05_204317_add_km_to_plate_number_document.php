<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204317_add_km_to_plate_number_document
class AddKmToPlateNumberDocument extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plate_numbers_documents', function (Blueprint $table) {
            $table->string('km')->nullable()->after('plate_number_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plate_number_documents', function (Blueprint $table) {
            $table->dropColumn('km');
        });
    }
}

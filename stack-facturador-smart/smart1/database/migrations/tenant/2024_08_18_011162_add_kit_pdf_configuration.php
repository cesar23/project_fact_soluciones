<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2024_08_18_011162_add_kit_pdf_configuration

class AddKitPdfConfiguration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('configurations', 'kit_pdf')) {
            Schema::table('configurations', function (Blueprint $table) {
                $table->boolean('kit_pdf')->default(false);
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
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('kit_pdf');
        });
    }
}

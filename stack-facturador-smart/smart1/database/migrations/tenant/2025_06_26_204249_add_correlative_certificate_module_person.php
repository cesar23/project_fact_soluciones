<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_06_26_204249_add_correlative_certificate_module_person
class AddCorrelativeCertificateModulePerson extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('certificates_person', function (Blueprint $table) {
            $table->string('series')->nullable();
            $table->unsignedInteger('number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('certificates_person', function (Blueprint $table) {
            $table->dropColumn('series');
            $table->dropColumn('number');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//2024_08_18_011183_add_subcontract_company_id

class AddSubcontractCompanyId extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('dispatches', 'subcontract_company_id')) {
            Schema::table('dispatches', function (Blueprint $table) {
                $table->unsignedInteger('subcontract_company_id')->nullable();
                $table->foreign('subcontract_company_id')->references('id')->on('persons');
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

        Schema::table('dispatches', function (Blueprint $table) {
            $table->dropForeign(['subcontract_company_id']);
            $table->dropColumn('subcontract_company_id');
        });
    }
}

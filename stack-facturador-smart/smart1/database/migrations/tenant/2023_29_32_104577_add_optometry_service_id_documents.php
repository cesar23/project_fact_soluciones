<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOptometryServiceIdDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('documents', 'optometry_service_id')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->unsignedInteger('optometry_service_id')->nullable();
                $table->foreign('optometry_service_id')->references('id')->on('optometry_services');
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
        //
        // Schema::table('documents', function (Blueprint $table) {
        //     $table->dropForeign(['optometry_service_id']);
        //     $table->dropColumn('optometry_service_id');
        // });
        if(Schema::hasColumn('documents', 'optometry_service_id')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->dropForeign(['optometry_service_id']);
                $table->dropColumn('optometry_service_id');
            });
        }

    }
}

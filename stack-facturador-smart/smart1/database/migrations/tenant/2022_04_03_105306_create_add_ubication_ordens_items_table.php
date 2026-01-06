<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddUbicationOrdensItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('orden_item', 'area_id')) {
            Schema::table('orden_item', function (Blueprint $table) {
                $table->unsignedInteger('area_id')->nullable()->after('status_orden_id');
                $table->foreign('area_id')->references('id')->on('areas');
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
        if (Schema::hasColumn('orden_item', 'area_id')) {
            Schema::table('orden_item', function (Blueprint $table) {
                $table->dropForeign(['area_id']);
                $table->dropColumn('area_id');
            });
        }
    }
}


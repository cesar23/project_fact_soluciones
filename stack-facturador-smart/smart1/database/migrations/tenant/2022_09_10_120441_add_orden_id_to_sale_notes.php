<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrdenIdToSaleNotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('sale_notes', 'orden_id')) {
            Schema::table('sale_notes', function (Blueprint $table) {
                $table->unsignedInteger('orden_id')->nullable()->after('establishment_id');
                $table->foreign('orden_id')->references('id')->on('ordens');
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
        if (Schema::hasColumn('sale_notes', 'orden_id')) {
            Schema::table('sale_notes', function (Blueprint $table) {
                $table->dropForeign(['orden_id']);
                $table->dropColumn('orden_id');
            });
        }
    }
}

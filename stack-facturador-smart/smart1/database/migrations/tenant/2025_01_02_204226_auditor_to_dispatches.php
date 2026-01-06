<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_01_02_204226_auditor_to_dispatches
class AuditorToDispatches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('auditor_history', 'dispatch_id')) {
            Schema::table('auditor_history', function (Blueprint $table) {
                $table->unsignedInteger('dispatch_id')->nullable();
                $table->foreign('dispatch_id')->references('id')->on('dispatches');
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
        Schema::table('auditor_history', function (Blueprint $table) {
            $table->dropForeign(['dispatch_id']);
            $table->dropColumn('dispatch_id');
        });
    }
}

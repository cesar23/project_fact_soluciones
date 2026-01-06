<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrdenIdToDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('documents', 'orden_id')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->unsignedInteger('orden_id')->nullable();
                $table->foreign('orden_id')->references('id')->on('ordens')->onDelete('cascade');
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
        if (Schema::hasColumn('documents', 'orden_id')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->dropForeign(['orden_id']);
                $table->dropColumn('orden_id');
            });
        }
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_01_02_204228_nulleable_document_id_in_auditor_history
class NulleableDocumentIdInAuditorHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('auditor_history', 'document_id')) {
            Schema::table('auditor_history', function (Blueprint $table) {
                $table->unsignedInteger('document_id')->nullable()->change();
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
            $table->unsignedInteger('document_id')->nullable(false)->change();
        });
    }
}

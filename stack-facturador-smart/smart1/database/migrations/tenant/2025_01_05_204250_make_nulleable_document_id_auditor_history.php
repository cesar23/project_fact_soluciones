<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204250_,make_nulleable_document_id_auditor_history

class MakeNulleableDocumentIdAuditorHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('auditor_history', function (Blueprint $table) {
            $table->unsignedInteger('document_id')->nullable()->change();
        });
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

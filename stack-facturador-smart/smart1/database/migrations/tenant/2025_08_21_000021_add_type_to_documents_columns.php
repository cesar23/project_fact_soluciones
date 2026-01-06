<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_08_21_000021_add_type_to_documents_columns
class AddTypeToDocumentsColumns extends Migration         
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('document_columns', function (Blueprint $table) {
            $table->string('type')->default('DOC');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('document_columns', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}

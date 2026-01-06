<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204237_add_fields_document_columns
class AddFieldsDocumentColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('document_columns', 'column_align') && !Schema::hasColumn('document_columns', 'column_order')) {
            Schema::table('document_columns', function (Blueprint $table) {
                $table->string('column_align', 10)->nullable();
                $table->tinyInteger('column_order')->nullable();
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
        Schema::table('document_columns', function (Blueprint $table) {
            $table->dropColumn('column_align');
            $table->dropColumn('column_order');
        });
    }
}

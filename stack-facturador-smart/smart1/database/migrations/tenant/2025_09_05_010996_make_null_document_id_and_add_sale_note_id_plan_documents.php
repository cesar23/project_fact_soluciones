<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_09_05_010996_make_null_document_id_and_add_sale_note_id_plan_documents
class MakeNullDocumentIdAndAddSaleNoteIdPlanDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('supply_plan_documents', function (Blueprint $table) {
            $table->unsignedInteger('sale_note_id')->nullable()->after('document_id');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('supply_plan_documents', function (Blueprint $table) {
            $table->dropColumn('sale_note_id');
        });
    }

    

    
}

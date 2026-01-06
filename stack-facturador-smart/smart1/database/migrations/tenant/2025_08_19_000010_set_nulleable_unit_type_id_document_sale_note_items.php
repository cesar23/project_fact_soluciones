<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_08_19_000010_set_nulleable_unit_type_id_document_sale_note_items
class SetNulleableUnitTypeIdDocumentSaleNoteItems extends Migration       
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('sale_note_items', 'unit_type_id')) {
            Schema::table('sale_note_items', function (Blueprint $table) {
                $table->string('unit_type_id')->nullable()->change();
            });
        }
        if (Schema::hasColumn('document_items', 'unit_type_id')) {
            Schema::table('document_items', function (Blueprint $table) {
                $table->string('unit_type_id')->nullable()->change();
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
        
    }
}

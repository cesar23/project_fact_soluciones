<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

//2025_07_16_153350_add_note_for_nv_document_type
class AddNoteForNvDocumentType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection('tenant')->table('cat_document_types')->insert([
            'id' => 'NVC',
            'description' => 'NOTA DE CRÉDITO - NOTA DE VENTA',
            'short' => 'NVC',
            'active' => true,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection('tenant')->table('cat_document_types')->where('id', 'NVC')->delete();
    }
}

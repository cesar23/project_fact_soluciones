<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_08_28_010004_insert_font_to_documents_pdf
class InsertFontToDocumentsPdf extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $to_insert = [
            [
                'document_type' => 'invoice',
                'format' => 'a4',
            ],
            [
                'document_type' => 'quotation',
                'format' => 'a4',
            ],
            [
                'document_type' => 'sale_note',
                'format' => 'a4',
            ],
            [
                'document_type' => 'purchase',
                'format' => 'a4',
            ],
            [
                'document_type' => 'dispatch',
                'format' => 'a4',
            ],
            [
                'document_type' => 'order_note',
                'format' => 'a4',
            ],
    
        ];
        DB::connection('tenant')->table('font_to_documents_pdf')->insert($to_insert);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection('tenant')->table('font_to_documents_pdf')->truncate();
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_08_28_010003_create_table_font_to_documents_pdf
class CreateTableFontToDocumentsPdf extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('font_to_documents_pdf', function (Blueprint $table) {
            $table->id();
            $table->string('document_type');
            $table->string('format');
            $table->string('font_size')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('font_to_documents_pdf');
    }
}
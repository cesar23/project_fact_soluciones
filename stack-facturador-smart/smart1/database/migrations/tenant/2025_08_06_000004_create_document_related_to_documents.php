<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_08_06_000004_create_document_related_to_documents
class CreateDocumentRelatedToDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_related_to_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('document_id');
            $table->string('related_document');
            $table->string('document_type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_related_to_documents');
    }
}
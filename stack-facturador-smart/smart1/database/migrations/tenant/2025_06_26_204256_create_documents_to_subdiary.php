<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_06_26_204256_create_documents_to_subdiary

class CreateDocumentsToSubdiary extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents_to_subdiary', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_sub_diary_id');
            $table->unsignedInteger('document_id')->nullable();
            $table->unsignedInteger('sale_note_id')->nullable();
            $table->unsignedInteger('purchase_id')->nullable();
            $table->timestamps();

            $table->foreign('account_sub_diary_id')->references('id')->on('account_sub_diaries');
            $table->foreign('document_id')->references('id')->on('documents');
            $table->foreign('sale_note_id')->references('id')->on('sale_notes');
            $table->foreign('purchase_id')->references('id')->on('purchases');
        });

    
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documents_to_subdiary');

        
    }
}

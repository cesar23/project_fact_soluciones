<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2024_12_30_000006_make_nulleable_document_number_sub_diary_item

class MakeNulleableDocumentNumberSubDiaryItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sub_diary_items', function (Blueprint $table) {
            $table->string('document_number')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sub_diary_items', function (Blueprint $table) {
            $table->string('document_number')->nullable(false)->change();
        });
    }
}
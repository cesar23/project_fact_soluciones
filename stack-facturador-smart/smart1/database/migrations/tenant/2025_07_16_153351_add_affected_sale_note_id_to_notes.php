<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

//2025_07_16_153351_add_affected_sale_note_id_to_notes
class AddAffectedSaleNoteIdToNotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
        Schema::table('notes', function (Blueprint $table) {
            $table->unsignedInteger('affected_sale_note_id')->nullable();
            $table->foreign('affected_sale_note_id')->references('id')->on('sale_notes');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropForeign(['affected_sale_note_id']);
            $table->dropColumn('affected_sale_note_id');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_08_19_000012_set_nulleable_presentation_item
class SetNulleablePresentationItem extends Migration       
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('sale_note_items', 'presentation_name')) {
            Schema::table('sale_note_items', function (Blueprint $table) {
                $table->string('presentation_name')->nullable()->change();
            });
        }
        if (Schema::hasColumn('document_items', 'presentation_name')) {
            Schema::table('document_items', function (Blueprint $table) {
                $table->string('presentation_name')->nullable()->change();
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

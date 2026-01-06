<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_08_21_000019_set_nulleable_unit_type_id_order_note_items
class SetNulleableUnitTypeIdOrderNoteItems extends Migration         
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('order_note_items', 'unit_type_id')) {
            Schema::table('order_note_items', function (Blueprint $table) {
                $table->string('unit_type_id')->nullable()->change();
            });
        }
        if (Schema::hasColumn('order_note_items', 'presentation_name')) {
            Schema::table('order_note_items', function (Blueprint $table) {
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

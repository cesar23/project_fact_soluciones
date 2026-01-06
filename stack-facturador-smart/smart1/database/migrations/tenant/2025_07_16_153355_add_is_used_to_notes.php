<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

//2025_07_16_153355_add_is_used_to_notes
class AddIsUsedToNotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
        Schema::table('notes', function (Blueprint $table) {
            $table->boolean('is_used')->default(false);
            $table->decimal('remaining_amount', 12, 2)->default(0);
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
            $table->dropColumn('is_used');
            $table->dropColumn('remaining_amount');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_08_21_000012_due_date_sale_note_qpos
class DueDateSaleNoteQpos extends Migration  
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('sale_notes') && !Schema::hasColumn('sale_notes', 'due_date')) {
            Schema::table('sale_notes', function (Blueprint $table) {
                $table->date('due_date')->nullable();
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
        if (Schema::hasTable('sale_notes') && Schema::hasColumn('sale_notes', 'due_date')) {
            Schema::table('sale_notes', function (Blueprint $table) {
                $table->dropColumn('due_date');
            });
        }
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


//2025_05_21_204265_add_to_config_show_notes_reception
class AddToConfigShowNotesReception extends Migration
{
    public function up()
    {
    

        if (!Schema::hasColumn('configurations', 'show_notes_reception')) {
            Schema::table('configurations', function (Blueprint $table) {
                $table->boolean('show_notes_reception')->default(false);
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('configurations', 'show_notes_reception')) {
            Schema::table('configurations', function (Blueprint $table) {
                $table->dropColumn('show_notes_reception');
            });
        }
    }
}
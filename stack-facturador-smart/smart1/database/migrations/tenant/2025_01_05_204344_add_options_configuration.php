<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204344_add_options_configuration
class AddOptionsConfiguration extends Migration
{
    public function up()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->boolean('search_packs_document_sale_note')->default(false);
            $table->boolean('show_web_platform_document_sale_note')->default(false);
        });
    }

    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('search_packs_document_sale_note');
            $table->dropColumn('show_web_platform_document_sale_note');
        });
    }
}

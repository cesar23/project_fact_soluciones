<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_08_25_010962_add_taxed_igv_visible_doc_cot
class AddTaxedIgvVisibleDocCot extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->boolean('taxed_igv_visible_doc')->default(true);
            $table->boolean('taxed_igv_visible_cot')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('taxed_igv_visible_doc');
            $table->dropColumn('taxed_igv_visible_cot');
        });
    
    }
}

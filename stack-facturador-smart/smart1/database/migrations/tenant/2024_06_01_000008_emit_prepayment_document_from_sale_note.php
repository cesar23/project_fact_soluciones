<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EmitPrepaymentDocumentFromSaleNote extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->unsignedInteger('emit_prepayment_document_from_sale_note')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('configurations', 'emit_prepayment_document_from_sale_note')) {
            Schema::table('configurations', function (Blueprint $table) {
                $table->dropColumn('emit_prepayment_document_from_sale_note');
            });
        }
        
    }
}

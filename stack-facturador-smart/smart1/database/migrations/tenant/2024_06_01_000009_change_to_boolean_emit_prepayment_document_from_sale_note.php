<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeToBooleanEmitPrepaymentDocumentFromSaleNote extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //eliminar la columna emit_prepayment_document_from_sale_note si existe 
        if (Schema::hasColumn('configurations', 'emit_prepayment_document_from_sale_note')) {
            Schema::table('configurations', function (Blueprint $table) {
                $table->dropColumn('emit_prepayment_document_from_sale_note');
            });
        }

        Schema::table('configurations', function (Blueprint $table) {
            $table->boolean('emit_prepayment_document_from_sale_note')->default(false);
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
            $table->dropColumn('emit_prepayment_document_from_sale_note');
        });
    }
}

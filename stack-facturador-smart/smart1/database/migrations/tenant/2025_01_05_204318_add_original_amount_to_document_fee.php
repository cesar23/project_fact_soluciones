<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204318_add_original_amount_to_document_fee
class AddOriginalAmountToDocumentFee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('document_fee', function (Blueprint $table) {
            $table->decimal('original_amount', 12, 2)->nullable()->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('document_fee', function (Blueprint $table) {
            $table->dropColumn('original_amount');
        });
    }
}

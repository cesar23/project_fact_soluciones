<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//2025_01_05_204288_add_cash_id_payments
class AddCashIdSaleNotePayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasColumn('sale_note_payments', 'cash_id')) {
            Schema::table('sale_note_payments', function (Blueprint $table) {
                $table->unsignedInteger('cash_id')->nullable();
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
        //
        Schema::table('sale_note_payments', function (Blueprint $table) {
            $table->dropColumn('cash_id');
        });
      
    }
}

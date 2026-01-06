<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//2025_01_05_204290_add_cash_id_quotation_payments
class AddCashIdQuotationPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasColumn('quotation_payments', 'cash_id')) {
            Schema::table('quotation_payments', function (Blueprint $table) {
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
        Schema::table('quotation_payments', function (Blueprint $table) {
            $table->dropColumn('cash_id');
        });
      
    }
}

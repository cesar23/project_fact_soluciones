<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIncomePaymentCashDocument extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cash_documents', function (Blueprint $table) {
            $table->unsignedInteger('income_payment_id')->nullable()->after('expense_payment_id');
            $table->foreign('income_payment_id')->references('id')->on('income_payments')->onDelete('cascade');
        });
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cash_documents', function (Blueprint $table) {
            $table->dropForeign(['income_payment_id']);
            $table->dropColumn('income_payment_id');  
        });
    }
}

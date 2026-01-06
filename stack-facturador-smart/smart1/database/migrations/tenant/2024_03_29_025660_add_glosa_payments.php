<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGlosaPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('document_payments', function (Blueprint $table) {
            $table->text('glosa')->nullable();
        });

        Schema::table('sale_note_payments', function (Blueprint $table) {
            $table->text('glosa')->nullable();
        });

        Schema::table('purchase_payments', function (Blueprint $table) {
            $table->text('glosa')->nullable();
        });

        Schema::table('quotation_payments', function (Blueprint $table) {
            $table->text('glosa')->nullable();
        });

        //expense_payments
        Schema::table('expense_payments', function (Blueprint $table) {
            $table->text('glosa')->nullable();
        });

        //income_payments
        Schema::table('income_payments', function (Blueprint $table) {
            $table->text('glosa')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('document_payments', function (Blueprint $table) {
            $table->dropColumn('glosa');
        });

        Schema::table('sale_note_payments', function (Blueprint $table) {
            $table->dropColumn('glosa');
        });

        Schema::table('purchase_payments', function (Blueprint $table) {
            $table->dropColumn('glosa');
        });

        Schema::table('quotation_payments', function (Blueprint $table) {
            $table->dropColumn('glosa');
        });

        //expense_payments
        Schema::table('expense_payments', function (Blueprint $table) {
            $table->dropColumn('glosa');
        });

        //income_payments
        Schema::table('income_payments', function (Blueprint $table) {
            $table->dropColumn('glosa');
        });
        
    }
}
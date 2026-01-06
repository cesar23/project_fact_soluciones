<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentConditionIdSaleNote extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('sale_notes', 'payment_condition_id')) {
            Schema::table('sale_notes', function (Blueprint $table) {
                $table->string('payment_condition_id')->nullable()->after('currency_type_id');

                $table->foreign('payment_condition_id')->references('id')->on('payment_conditions');
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
        Schema::table('sale_notes', function (Blueprint $table) {
            $table->dropForeign(['payment_condition_id']);
            $table->dropColumn('payment_condition_id');
        });
    }
}

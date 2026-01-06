<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentMethodTypeIdSaleNoteFee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('sale_note_fee', 'payment_method_type_id')) {
            Schema::table('sale_note_fee', function (Blueprint $table) {
                $table->char('payment_method_type_id', 2)->nullable()->after('currency_type_id');
                $table->foreign('payment_method_type_id')->references('id')->on('payment_method_types');
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
        Schema::table('sale_note_fee', function (Blueprint $table) {
            $table->dropForeign(['payment_method_type_id']);
            $table->dropColumn('payment_method_type_id');
        });
    }
}

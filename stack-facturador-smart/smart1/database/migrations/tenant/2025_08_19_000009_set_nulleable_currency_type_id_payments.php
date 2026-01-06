<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_08_19_000009_set_nulleable_currency_type_id_payments
class SetNulleableCurrencyTypeIdPayments extends Migration       
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('document_payments', 'currency_type_id')) {
            Schema::table('document_payments', function (Blueprint $table) {
                $table->string('currency_type_id')->nullable()->change();
            });
        }

        if (Schema::hasColumn('sale_note_payments', 'currency_type_id')) {
            Schema::table('sale_note_payments', function (Blueprint $table) {
                $table->string('currency_type_id')->nullable()->change();
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
        
    }
}

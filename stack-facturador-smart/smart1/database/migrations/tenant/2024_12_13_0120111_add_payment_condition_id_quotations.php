<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

//2024_12_13_0120111_add_payment_condition_id_quotations.php

class AddPaymentConditionIdQuotations extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quotations', function (Blueprint $table) {
            if (!Schema::hasColumn('quotations', 'payment_condition_id')) {
                $table->string('payment_condition_id')->default('01')->after('currency_type_id');
                $table->foreign('payment_condition_id')->references('id')->on('payment_conditions');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quotations', function (Blueprint $table) {
            if (Schema::hasColumn('quotations', 'payment_condition_id')) {
                $table->dropForeign(['payment_condition_id']);
                $table->dropColumn('payment_condition_id');
            }
        });
    }
}

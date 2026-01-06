<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2024_12_14_000003_add_configuration_credit_default_quotation
class AddConfigurationCreditDefaultQuotation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('configurations', 'credit_default_quotation')) {
            Schema::table('configurations', function (Blueprint $table) {
                $table->boolean('credit_default_quotation')->default(false);
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
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('credit_default_quotation');
        });
    }
}
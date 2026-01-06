<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class AddCashIdDocumentPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasColumn('document_payments', 'cash_id')) {
            Schema::table('document_payments', function (Blueprint $table) {
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
        Schema::table('document_payments', function (Blueprint $table) {
            $table->dropColumn('cash_id');
        });
      
    }
}

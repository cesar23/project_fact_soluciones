<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsPaymentMethods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_method_types', function (Blueprint $table) {
            $table->boolean('is_digital')->default(false);
            $table->boolean('is_bank')->default(false);
          
        });
        // //categories
        // Schema::table('documents', function (Blueprint $table) {
        //     $table->unsignedInteger('cash_id')->nullable();
        //     $table->foreign('cash_id')->references('id')->on('cash');
        // });

        // Schema::table('sale_notes', function (Blueprint $table) {
        //     $table->unsignedInteger('cash_id')->nullable();
        //     $table->foreign('cash_id')->references('id')->on('cash');
        // });
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_method_types', function (Blueprint $table) {
            $table->dropColumn('is_digital');
            $table->dropColumn('is_bank');
        });
        // Schema::table('documents', function (Blueprint $table) {
        //     $table->dropForeign(['cash_id']);
        //     $table->dropColumn('cash_id');
        // });
        // Schema::table('sale_notes', function (Blueprint $table) {
        //     $table->dropForeign(['cash_id']);
        //     $table->dropColumn('cash_id');
        // });
    }
}

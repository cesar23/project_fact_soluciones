<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsPurchaseOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {

            $table->unsignedInteger('created_by_id')->nullable();
            $table->unsignedInteger('approved_by_id')->nullable();
            $table->unsignedInteger('quotation_id')->nullable();
            $table->foreign('created_by_id')->references('id')->on('users');
            $table->foreign('approved_by_id')->references('id')->on('users');
            $table->foreign('quotation_id')->references('id')->on('quotations');
            $table->string('client_internal_id')->nullable();
            $table->enum('type', ['goods', 'services'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['created_by_id']);
            $table->dropForeign(['approved_by_id']);
            $table->dropForeign(['quotation_id']);
            $table->dropColumn('created_by_id');
            $table->dropColumn('client_internal_id');
            $table->dropColumn('approved_by_id');
            $table->dropColumn('type');
        });
    }
}

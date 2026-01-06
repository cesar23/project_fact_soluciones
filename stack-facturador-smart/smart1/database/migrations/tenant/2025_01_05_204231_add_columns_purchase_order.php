<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//2025_01_05_204231_add_columns_purchase_order
class AddColumnsPurchaseOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('purchase_orders', 'bank_name') && !Schema::hasColumn('purchase_orders', 'bank_account_number')) {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->string('bank_name')->nullable();
                $table->string('bank_account_number')->nullable();
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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('bank_name');
            $table->dropColumn('bank_account_number');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentTrackingToSupplyDebt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('supply_debt', function (Blueprint $table) {
            $table->decimal('paid_amount', 10, 2)->default(0)->after('original_amount');
            $table->timestamp('last_payment_date')->nullable()->after('paid_amount');
            $table->integer('payment_count')->default(0)->after('last_payment_date');
            $table->decimal('cancelled_amount', 10, 2)->default(0)->after('payment_count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('supply_debt', function (Blueprint $table) {
            $table->dropColumn([
                'paid_amount',
                'last_payment_date', 
                'payment_count',
                'cancelled_amount'
            ]);
        });
    }
}
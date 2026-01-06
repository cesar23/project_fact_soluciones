<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentDetailsToSupplyDebtDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('supply_debt_documents', function (Blueprint $table) {
            $table->decimal('amount_paid', 10, 2)->after('supply_plan_document_id');
            $table->decimal('debt_amount_before', 10, 2)->after('amount_paid');
            $table->decimal('debt_amount_after', 10, 2)->after('debt_amount_before');
            $table->enum('payment_type', ['partial', 'full'])->after('debt_amount_after');
            $table->boolean('is_cancelled')->default(false)->after('payment_type');
            $table->timestamp('cancelled_at')->nullable()->after('is_cancelled');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('supply_debt_documents', function (Blueprint $table) {
            $table->dropColumn([
                'amount_paid',
                'debt_amount_before',
                'debt_amount_after',
                'payment_type',
                'is_cancelled',
                'cancelled_at'
            ]);
        });
    }
}
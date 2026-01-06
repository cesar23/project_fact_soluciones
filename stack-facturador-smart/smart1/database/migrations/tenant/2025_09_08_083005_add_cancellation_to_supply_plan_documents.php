<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCancellationToSupplyPlanDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('supply_plan_documents', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable()->after('observations');
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('cancelled_at');
            $table->text('cancellation_reason')->nullable()->after('cancelled_by');
            $table->string('original_status')->nullable()->after('cancellation_reason');
            $table->boolean('is_debt_payment')->default(false)->after('original_status');
            
            // No crear la foreign key constraint debido a problemas con tenant database
            // $table->foreign('cancelled_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('supply_plan_documents', function (Blueprint $table) {
            // $table->dropForeign(['cancelled_by']); // No se creó la foreign key
            $table->dropColumn([
                'cancelled_at',
                'cancelled_by',
                'cancellation_reason',
                'original_status',
                'is_debt_payment'
            ]);
        });
    }
}
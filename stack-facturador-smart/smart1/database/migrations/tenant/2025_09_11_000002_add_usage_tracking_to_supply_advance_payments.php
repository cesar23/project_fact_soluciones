<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUsageTrackingToSupplyAdvancePayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('supply_advance_payments', function (Blueprint $table) {
            $table->boolean('is_used')->default(false)->after('document_type_id');
            $table->unsignedBigInteger('used_in_document_id')->nullable()->after('is_used');
            $table->timestamp('used_at')->nullable()->after('used_in_document_id');
            
            // Indexes for better performance
            $table->index(['supply_id', 'is_used']);
            $table->index('used_in_document_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('supply_advance_payments', function (Blueprint $table) {
            $table->dropIndex(['supply_id', 'is_used']);
            $table->dropIndex(['used_in_document_id']);
            $table->dropColumn([
                'is_used',
                'used_in_document_id', 
                'used_at'
            ]);
        });
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPlanChangeFieldsToSuppliesPlansRegistered extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('supplies_plans_registered', function (Blueprint $table) {
            $table->date('date_start')->nullable()->after('active');
            $table->date('date_end')->nullable()->after('date_start');
            $table->text('change_reason')->nullable()->after('date_end');
            $table->unsignedBigInteger('previous_plan_registered_id')->nullable()->after('change_reason');
            $table->foreign('previous_plan_registered_id')->references('id')->on('supplies_plans_registered');
            $table->index(['supply_id', 'active']);
            $table->index(['date_start', 'date_end']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('supplies_plans_registered', function (Blueprint $table) {
            $table->dropForeign(['previous_plan_registered_id']);
            $table->dropColumn(['date_start', 'date_end', 'change_reason', 'previous_plan_registered_id']);
        });
    }
}
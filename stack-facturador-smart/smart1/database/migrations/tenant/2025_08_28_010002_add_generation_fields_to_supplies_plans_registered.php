<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGenerationFieldsToSuppliesPlansRegistered extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('supplies_plans_registered', function (Blueprint $table) {
            $table->integer('generation_day')->default(1)->after('active');
            $table->boolean('auto_generate')->default(false)->after('generation_day');
            $table->date('start_generation_date')->nullable()->after('auto_generate');
            $table->date('end_generation_date')->nullable()->after('start_generation_date');
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
            $table->dropColumn(['generation_day', 'auto_generate', 'start_generation_date', 'end_generation_date']);
        });
    }
}
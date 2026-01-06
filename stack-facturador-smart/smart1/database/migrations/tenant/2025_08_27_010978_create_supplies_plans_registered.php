<?php

use App\Models\Tenant\DocumentColumn;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_08_27_010978_create_supplies_plans_registered
class CreateSuppliesPlansRegistered extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplies_plans_registered', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('supplie_plan_id');
            $table->unsignedInteger('supply_id');
            $table->unsignedInteger('user_id');
            $table->string('contract_number')->nullable();
            $table->text('observation')->nullable();
            $table->boolean('active');
            $table->timestamps();
            $table->index(['supplie_plan_id', 'supply_id']);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supplies_plans_registered');
    }
}

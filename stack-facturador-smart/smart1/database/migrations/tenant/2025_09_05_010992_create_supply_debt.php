<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_09_05_010992_create_supply_debt
class CreateSupplyDebt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supply_debt', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supply_contract_id')->nullable();
            $table->unsignedInteger('person_id')->nullable();
            $table->unsignedBigInteger('supply_id')->nullable();
            $table->string('serie_receipt')->nullable();
            $table->unsignedInteger('correlative_receipt')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('year')->nullable();
            $table->string('month')->nullable();
            $table->date('generation_date')->nullable();
            $table->date('due_date')->nullable();
            $table->boolean('active')->default(true);
            $table->string('type')->nullable();
            $table->unsignedInteger('supply_type_debt_id')->nullable();
            $table->unsignedInteger('supply_concept_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supply_debt');
    }

    
}

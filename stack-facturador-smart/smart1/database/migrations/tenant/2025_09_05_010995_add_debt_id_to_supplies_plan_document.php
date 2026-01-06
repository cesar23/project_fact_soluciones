<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_09_05_010995_add_debt_id_to_supplies_plan_document
class AddDebtIdToSuppliesPlanDocument extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supply_debt_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('debt_id')->nullable();
            $table->unsignedBigInteger('supply_plan_document_id')->nullable();
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
        Schema::dropIfExists('supply_debt_documents');
    }
    }

    

    

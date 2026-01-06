<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_09_05_010995_add_original_amount_to_debt
class AddOriginalAmountToDebt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('supply_debt', function (Blueprint $table) {
            $table->decimal('original_amount', 10, 2)->nullable();
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
            $table->dropColumn('original_amount');
        });
    }

    

    
}

<?php

use App\Models\Tenant\Cash;
use App\Models\Tenant\DocumentPayment;
use App\Models\Tenant\SaleNotePayment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_08_06_000007_add_is_individual_to_user_rel_suscription_plans
class AddIsIndividualToUserRelSuscriptionPlans extends Migration 
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_rel_suscription_plans', function (Blueprint $table) {
            $table->boolean('is_individual')->default(false);
        });

    
        
    }
    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_rel_suscription_plans', function (Blueprint $table) {
            $table->dropColumn('is_individual');
        });
    }
}
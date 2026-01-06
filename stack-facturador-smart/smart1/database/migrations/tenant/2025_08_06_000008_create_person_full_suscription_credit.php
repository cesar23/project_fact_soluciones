<?php

use App\Models\Tenant\Cash;
use App\Models\Tenant\DocumentPayment;
use App\Models\Tenant\SaleNotePayment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_08_06_000008_create_person_full_suscription_credit
class CreatePersonFullSuscriptionCredit extends Migration 
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('person_full_suscription_credit', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('person_id');
            $table->decimal('amount', 10, 2);
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
        Schema::dropIfExists('person_full_suscription_credit');
    }
}
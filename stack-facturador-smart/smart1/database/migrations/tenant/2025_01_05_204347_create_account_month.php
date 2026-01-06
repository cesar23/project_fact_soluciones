<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204347_create_account_month
class CreateAccountMonth extends Migration
{
    public function up()
    {
        Schema::create('account_months', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_period_id')->constrained('account_periods');
            $table->string('month');
            $table->decimal('balance', 15, 2);
            $table->decimal('total_debit', 15, 2);
            $table->decimal('total_credit', 15, 2);
            $table->timestamps();
            
        });
    }

    public function down()
    {
        Schema::dropIfExists('account_months');
    }
}

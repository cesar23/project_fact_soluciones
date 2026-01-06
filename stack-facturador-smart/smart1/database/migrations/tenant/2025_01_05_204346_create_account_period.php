<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204346_create_account_period
class CreateAccountPeriod extends Migration
{
    public function up()
    {
        Schema::create('account_periods', function (Blueprint $table) {
            $table->id();
            $table->string('year');
            $table->decimal('total_debit', 15, 2);
            $table->decimal('total_credit', 15, 2);
            $table->decimal('balance', 15, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('account_periods');
    }
}

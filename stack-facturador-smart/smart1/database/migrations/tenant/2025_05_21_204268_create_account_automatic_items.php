<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


//2025_05_21_204267_create_account_automatic
class CreateAccountAutomaticItems extends Migration
{
    public function up()
    {
    

        Schema::create('account_automatic_items', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->boolean('is_debit');
            $table->boolean('is_credit');
            $table->foreignId('account_automatic_id')->constrained('account_automatic');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('account_automatic_items');
    }
}
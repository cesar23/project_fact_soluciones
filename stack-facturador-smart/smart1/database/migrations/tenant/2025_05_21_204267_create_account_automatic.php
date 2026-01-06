<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


//2025_05_21_204267_create_account_automatic
class CreateAccountAutomatic extends Migration
{
    public function up()
    {
    

        Schema::create('account_automatic', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->string('type');
            $table->boolean('active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('account_automatic');
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRespondents extends Migration
{
    public function up()
    {
        Schema::create('respondents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('number')->nullable();
            $table->enum('sex', ['male','female'])->default('male');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('uuid')->unique();
            $table->string('password');
            $table->date('dob')->nullable();
            $table->string('address')->nullable();
            $table->string('country_id')->nullable();
            $table->string('department_id')->nullable();
            $table->string('province_id')->nullable();
            $table->string('district_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('respondents');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMultipayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('multipayments', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('payment', 12, 2);
            $table->decimal('remaining', 12, 2);
            $table->date('date_of_issue');
            $table->unsignedInteger('user_id');
            
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        Schema::dropIfExists('multipayments');
        
        
    }
}

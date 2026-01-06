<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advances', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('person_id');
            $table->date('date_opening');
            $table->time('time_opening');
            $table->date('date_closed')->nullable();
            $table->time('time_closed')->nullable();
            $table->decimal('beginning_balance', 12, 4)->default(0);
            $table->decimal('final_balance', 12, 4)->default(0);
            $table->boolean('state')->default(false);
            $table->string('reference_number', 250)->nullable();
            $table->timestamps();
            $table->foreign('person_id')->references('id')->on('persons');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advances');
    }
}

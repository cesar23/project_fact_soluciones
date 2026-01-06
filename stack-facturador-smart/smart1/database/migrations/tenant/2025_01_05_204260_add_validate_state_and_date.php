<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204260_add_validate_state_and_date

class AddValidateStateAndDate extends Migration
{
    /**
     * Run the migrations.

     *
     * @return void
     */
    public function up()
    {


        Schema::table('documents', function (Blueprint $table) {
            $table->string('state_validate')->nullable();
            $table->date('date_validate')->nullable();
        });




    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('state_validate');
            $table->dropColumn('date_validate');
        });

      


    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204258_add_attempt_pse_documents

class AddAttemptPseDocuments extends Migration
{
    /**
     * Run the migrations.

     *
     * @return void
     */
    public function up()
    {


        Schema::table('documents', function (Blueprint $table) {
            $table->tinyInteger('attempt_pse')->default(0);
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
            $table->dropColumn('attempt_pse');
        });

      

    }
}

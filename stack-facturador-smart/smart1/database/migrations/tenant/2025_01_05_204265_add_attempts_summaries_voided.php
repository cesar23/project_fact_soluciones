<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;



class AddAttemptsSummariesVoided extends Migration
{
    public function up()
    {

        
        Schema::table('summaries', function (Blueprint $table) {
            $table->tinyInteger('attempt_pse')->default(0);
        });

        Schema::table('voided', function (Blueprint $table) {
            $table->tinyInteger('attempt_pse')->default(0);
        });


    }

    public function down()
    {
        Schema::table('summaries', function (Blueprint $table) {
            $table->dropColumn('attempt_pse');
        });

        Schema::table('voided', function (Blueprint $table) {
            $table->dropColumn('attempt_pse');
        });
    }


}
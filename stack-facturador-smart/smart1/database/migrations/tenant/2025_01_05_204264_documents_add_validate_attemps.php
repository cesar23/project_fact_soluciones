<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class DocumentsAddValidateAttemps extends Migration
{
    public function up()
    {
        
        Schema::table('documents', function (Blueprint $table) {
            $table->tinyInteger('validate_attemps')->default(0);
        });

    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('validate_attemps');
        });
    }

}
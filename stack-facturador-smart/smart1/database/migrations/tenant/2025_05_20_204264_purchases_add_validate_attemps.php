<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class PurchasesAddValidateAttemps extends Migration
{
    public function up()
    {
        
        Schema::table('purchases', function (Blueprint $table) {
            $table->tinyInteger('validate_attemps')->default(0);
        });

    }

    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('validate_attemps');
        });
    }

}
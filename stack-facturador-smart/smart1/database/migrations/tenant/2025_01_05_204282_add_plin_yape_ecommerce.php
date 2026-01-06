<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


//2025_01_05_204282_add_plin_yape_ecommerce

class AddPlinYapeEcommerce extends Migration 
{
    public function up()
    {
        Schema::table('configuration_ecommerce', function (Blueprint $table) {
            $table->string('plin_number', 150)->nullable();
            $table->string('plin_name', 150)->nullable();
            $table->string('plin_qr', 150)->nullable();
            $table->string('yape_number', 150)->nullable();
            $table->string('yape_name', 150)->nullable();
            $table->string('yape_qr', 150)->nullable();
        });
    


    }

    public function down()
    {
        Schema::table('configuration_ecommerce', function (Blueprint $table) {
            $table->dropColumn('plin_number');
            $table->dropColumn('plin_name');
            $table->dropColumn('plin_qr');
            $table->dropColumn('yape_number');
            $table->dropColumn('yape_name');
            $table->dropColumn('yape_qr');
        });
    }


}
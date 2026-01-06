<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMultiCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('multi_companies', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('client_id');
            $table->tinyInteger('hostname_id');
            $table->tinyInteger('website_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
                                               
        Schema::dropIfExists('multi_companies');            
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyNotPrincipalChoose extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies',function (Blueprint $table){
            $table->boolean('not_principal')->default(false);
            $table->boolean('choose')->default(false);
            $table->tinyInteger('website_id')->nullable();
            $table->json('document_number')->nullable();
        });


    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies',function (Blueprint $table){
            $table->dropColumn('not_principal');
            $table->dropColumn('choose');
            $table->dropColumn('website_id');
            $table->dropColumn('document_number');
        });

    }
}

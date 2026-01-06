<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddObservationsQuotationProject extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::table('quotation_projects', function (Blueprint $table) {
            $table->longText('observations')->nullable()->after('percentage');
        });

    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

       
        Schema::table('quotation_projects', function (Blueprint $table) {
            $table->dropColumn('observations');
        });
    
    }
}

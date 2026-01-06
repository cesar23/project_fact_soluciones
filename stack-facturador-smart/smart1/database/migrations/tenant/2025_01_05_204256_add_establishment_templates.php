<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204256_add_establishment_templates

class AddEstablishmentTemplates extends Migration
{
    /**
     * Run the migrations.

     *
     * @return void
     */
    public function up()
    {


        Schema::table('establishments', function (Blueprint $table) {
            $table->string('template_documents')->nullable();
            $table->string('template_sale_notes')->nullable();
            $table->string('template_dispatches')->nullable();
            $table->string('template_quotations')->nullable();
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
        Schema::table('establishments', function (Blueprint $table) {
            $table->dropColumn('template_documents');
            $table->dropColumn('template_sale_notes');
            $table->dropColumn('template_dispatches');
            $table->dropColumn('template_quotations');
        });

      

    }
}

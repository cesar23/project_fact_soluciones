<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204256_add_establishment_templates

class AddEstablishmentTemplatesTicket extends Migration
{
    /**
     * Run the migrations.

     *
     * @return void
     */
    public function up()
    {


        Schema::table('establishments', function (Blueprint $table) {
            $table->string('template_documents_ticket')->nullable();
            $table->string('template_sale_notes_ticket')->nullable();
            $table->string('template_dispatches_ticket')->nullable();
            $table->string('template_quotations_ticket')->nullable();
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
            $table->dropColumn('template_documents_ticket');
            $table->dropColumn('template_sale_notes_ticket');
            $table->dropColumn('template_dispatches_ticket');
            $table->dropColumn('template_quotations_ticket');
        });

      

    }
}

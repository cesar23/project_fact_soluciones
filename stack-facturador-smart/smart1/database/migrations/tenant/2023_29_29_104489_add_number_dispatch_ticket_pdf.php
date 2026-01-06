<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNumberDispatchTicketPdf extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::table('documents', function (Blueprint $table) {
            $table->tinyInteger('dispatch_ticket_pdf_quantity')->after('dispatch_ticket_pdf')->default(1);
        });

        
        Schema::table('sale_notes', function (Blueprint $table) {
            $table->tinyInteger('dispatch_ticket_pdf_quantity')->after('dispatch_ticket_pdf')->default(1);
        });
        Schema::table('order_notes', function (Blueprint $table) {
            $table->string('reference_data')->nullable()->after('additional_data');
            $table->boolean('dispatch_ticket_pdf')->default(false)->after('additional_data');
            $table->tinyInteger('dispatch_ticket_pdf_quantity')->after('dispatch_ticket_pdf')->default(1);

        });


    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('dispatch_ticket_pdf_quantity');
        });

        Schema::table('sale_notes', function (Blueprint $table) {
            $table->dropColumn('dispatch_ticket_pdf_quantity');
        });
        Schema::table('order_notes', function (Blueprint $table) {
            $table->dropColumn('reference_data');
            $table->dropColumn('dispatch_ticket_pdf');
            $table->dropColumn('dispatch_ticket_pdf_quantity');
        });
      
      
    }
}

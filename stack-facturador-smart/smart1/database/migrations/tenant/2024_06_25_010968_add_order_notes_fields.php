<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderNotesFields extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_notes', function (Blueprint $table) {
            $table->string('payment_condition_document_id')->nullable();
            $table->string('number_transport_company')->nullable();
            $table->string('name_transport_company')->nullable();
            $table->tinyInteger('state')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {   
        Schema::table('order_notes', function (Blueprint $table) {
            $table->dropColumn('payment_condition_document_id');
            $table->dropColumn('number_transport_company');
            $table->dropColumn('name_transport_company');
            $table->dropColumn('state');
        });

    
    }
}

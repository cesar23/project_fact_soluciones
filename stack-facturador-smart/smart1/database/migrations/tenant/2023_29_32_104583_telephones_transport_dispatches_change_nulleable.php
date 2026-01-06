<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TelephonesTransportDispatchesChangeNulleable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('document_transport_dispatches', function (Blueprint $table) {
            $table->string('sender_telephone')->nullable()->change();
            $table->string('recipient_telephone')->nullable()->change();
        });

        Schema::table('sale_note_transport_dispatches', function (Blueprint $table) {
            $table->string('sender_telephone')->nullable()->change();
            $table->string('recipient_telephone')->nullable()->change();
        });
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('document_transport_dispatches', function (Blueprint $table) {
            $table->string('sender_telephone')->nullable(false)->change();
            $table->string('recipient_telephone')->nullable(false)->change();
        });

        Schema::table('sale_note_transport_dispatches', function (Blueprint $table) {
            $table->string('sender_telephone')->nullable(false)->change();
            $table->string('recipient_telephone')->nullable(false)->change();
        });
    
    }
}

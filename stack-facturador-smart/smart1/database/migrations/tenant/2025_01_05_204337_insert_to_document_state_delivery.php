<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204337_insert_to_document_state_delivery
class InsertToDocumentStateDelivery extends Migration
{
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->unsignedInteger('state_delivery_id')->nullable();
            $table->foreign('state_delivery_id')->references('id')->on('state_deliveries')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['state_delivery_id']);
            $table->dropColumn('state_delivery_id');
        });
    }
}
    
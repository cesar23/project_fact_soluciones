<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DocumentTypeIdOrderNotes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('order_notes', 'document_type_id')) {
            Schema::table('order_notes', function (Blueprint $table) {
                $table->string('document_type_id')->nullable()->change();
            });
        }

        if (Schema::hasColumn('order_notes', 'series')) {
            Schema::table('order_notes', function (Blueprint $table) {
                $table->string('series')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}

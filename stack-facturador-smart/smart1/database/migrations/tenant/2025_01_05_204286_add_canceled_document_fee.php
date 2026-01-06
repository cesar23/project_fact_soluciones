<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCanceledDocumentFee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasColumn('document_fee', 'is_canceled')) {
            Schema::table('document_fee', function (Blueprint $table) {
                $table->boolean('is_canceled')->default(false);
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
        //
        Schema::table('document_fee', function (Blueprint $table) {
            $table->dropColumn('is_canceled');
        });
      
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSireAppendixDocument extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->boolean('appendix_2')->default(true);
            $table->boolean('appendix_3')->default(true);
            $table->boolean('appendix_4')->default(true);
            $table->boolean('appendix_5')->default(true);
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
            $table->dropColumn('appendix_2');
            $table->dropColumn('appendix_3');
            $table->dropColumn('appendix_4');
            $table->dropColumn('appendix_5');
        });
    }
}

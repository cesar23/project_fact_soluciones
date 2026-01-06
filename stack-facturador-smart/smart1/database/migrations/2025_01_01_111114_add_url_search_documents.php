<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

//2025_01_01_111114_add_url_search_documents


class AddUrlSearchDocuments extends Migration
{
    /**
     * Run the migrations.




     *
     * @return void
     */

    public function up()
    {
    
        Schema::table('configurations', function (Blueprint $table) {
            $table->string('url_search_documents')->nullable();
        });



    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('url_search_documents');
        });



    }
}

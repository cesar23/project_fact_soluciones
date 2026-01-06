<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConfigurationEditInfoDocumentsFavorites extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('configurations',function (Blueprint $table){
            $table->boolean('edit_info_documents')->default(false);
            $table->boolean('show_favorites_documents')->default(false);
      
        });

    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('configurations',function (Blueprint $table){
          $table->dropColumn('edit_info_documents');
          $table->dropColumn('show_favorites_documents');
      });

    }
}

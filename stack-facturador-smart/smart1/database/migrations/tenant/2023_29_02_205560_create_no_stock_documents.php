<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNoStockDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
        Schema::create('no_stock_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('document_id')->nullable();
            $table->unsignedInteger('sale_note_id')->nullable();
            $table->boolean('completed')->default(false);
            $table->timestamps();
        });

        Schema::table('documents', function (Blueprint $table) {
            
            $table->boolean('no_stock')->default(false);
        });

        
        Schema::table('sale_notes', function (Blueprint $table) {
            
            $table->boolean('no_stock')->default(false);
        });

        Schema::table('configurations', function (Blueprint $table) {
            
            $table->boolean('document_no_stock')->default(false);
        });

    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('no_stock_documents');
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('no_stock');
        });
        Schema::table('sale_notes', function (Blueprint $table) {
            $table->dropColumn('no_stock');
        });
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('document_no_stock');
        });
    }
}

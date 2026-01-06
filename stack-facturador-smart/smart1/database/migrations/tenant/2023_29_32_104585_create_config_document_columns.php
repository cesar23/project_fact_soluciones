<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigDocumentColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_columns', function (Blueprint $table) {
            $table->id();
            $table->string('value');
            $table->string('name');
            $table->decimal('width', 8, 2);
            $table->integer('order')->default(0);
            $table->boolean('is_visible')->default(false);
        });

    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_columns');
    
    }
}

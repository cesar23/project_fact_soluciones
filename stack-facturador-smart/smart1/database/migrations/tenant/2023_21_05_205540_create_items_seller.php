<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsSeller extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('items_sellers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('document_item_id')->nullable();
            $table->unsignedInteger('sale_note_item_id')->nullable();
            $table->unsignedInteger('seller_id');
            $table->foreign('sale_note_item_id')->references('id')->on('sale_note_items');
            $table->foreign('document_item_id')->references('id')->on('document_items');
            $table->foreign('seller_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items_sellers');
    }
}

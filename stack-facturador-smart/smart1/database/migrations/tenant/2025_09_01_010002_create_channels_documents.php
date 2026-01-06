<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_09_01_010002_create_channels_documents
class CreateChannelsDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channels_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('channel_reg_id');
            $table->unsignedInteger('document_id')->nullable();
            $table->unsignedInteger('dispatch_id')->nullable();
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
        Schema::dropIfExists('channels_documents');
    }
};
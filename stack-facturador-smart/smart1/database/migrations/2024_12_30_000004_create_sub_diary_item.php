<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2024_12_30_000004_create_sub_dairy_item

class CreateSubDiaryItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_diary_items', function (Blueprint $table) {
            $table->id();
            $table->string('sub_diary_code');
            $table->string('code');
            $table->string('description');
            $table->string('document_number');
            $table->string('correlative_number');
            $table->boolean('debit')->default(false);
            $table->boolean('credit')->default(false);
            $table->decimal('debit_amount', 10, 2);
            $table->decimal('credit_amount', 10, 2);
            $table->foreign('sub_diary_code')->references('code')->on('sub_diaries');
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
        Schema::dropIfExists('sub_diary_items');
    }
}
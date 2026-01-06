<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2024_12_30_000004_create_sub_dairy_item

class CreateSubDiariesItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_sub_diary_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_sub_diary_id');
            $table->string('code')->index();
            $table->string('description');
            $table->string('general_description')->index();
            $table->string('document_number')->nullable()->index();
            $table->string('correlative_number');
            $table->boolean('debit')->default(false);
            $table->boolean('credit')->default(false);
            $table->decimal('debit_amount', 10, 2);
            $table->decimal('credit_amount', 10, 2);
            $table->foreign('account_sub_diary_id')->references('id')->on('account_sub_diaries');
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
        Schema::dropIfExists('account_sub_diary_items');
    }
}
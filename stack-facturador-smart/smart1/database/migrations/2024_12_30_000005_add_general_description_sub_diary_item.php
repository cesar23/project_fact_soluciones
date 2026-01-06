<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2024_12_30_000005_add_general_description_sub_diary_item

class AddGeneralDescriptionSubDiaryItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sub_diary_items', function (Blueprint $table) {
            $table->string('general_description')->after('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sub_diary_items', function (Blueprint $table) {
            $table->dropColumn('general_description');
        });
    }
}
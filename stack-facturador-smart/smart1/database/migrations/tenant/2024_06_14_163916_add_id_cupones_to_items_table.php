<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIdCuponesToItemsTable extends Migration
{

    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->unsignedBigInteger('id_cupones')->nullable()->after('is_food_dealer');
        });
    }

    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('id_cupones');
        });
    }
}

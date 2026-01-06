<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddAreaIdFoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('foods', 'area_id')) {
            Schema::table('foods', function (Blueprint $table) {
                $table->unsignedInteger('area_id')->nullable()->after('image');
                $table->foreign('area_id')->references('id')->on('areas');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('foods', 'area_id')) {
            Schema::table('foods', function (Blueprint $table) {
                $table->dropColumn('area_id');
            });
        }
    }
}

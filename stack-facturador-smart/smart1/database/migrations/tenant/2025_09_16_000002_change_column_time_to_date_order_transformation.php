<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_09_16_000002_change_column_time_to_date_order_transformation
class ChangeColumnTimeToDateOrderTransformation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_transformations', function (Blueprint $table) {
            $table->time('prod_start_time')->nullable()->change();
            $table->time('prod_end_time')->nullable()->change();
            $table->time('mix_start_time')->nullable()->change();
            $table->time('mix_end_time')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_transformations', function (Blueprint $table) {
            $table->time('prod_start_time')->nullable()->change();
            $table->time('prod_end_time')->nullable()->change();
            $table->time('mix_start_time')->nullable()->change();
            $table->time('mix_end_time')->nullable()->change();
        });
    }
}
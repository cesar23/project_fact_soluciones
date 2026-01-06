<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_05_28_205546_add_batch_number_orden_item
class AddBatchNumberOrdenItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orden_item', function (Blueprint $table) {
            $table->tinyInteger('batch_number')->nullable()->default(1);
        });
    }   



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orden_item', function (Blueprint $table) {
            $table->dropColumn('batch_number');
        });
    }
}

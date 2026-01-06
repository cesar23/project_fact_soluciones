<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSunatStockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
        Schema::create('sunat_stock', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('start_stock', 12, 2)->default(0);
            $table->decimal('end_stock', 12, 2)->default(0);
            $table->date('period');
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('warehouse_id')->nullable();
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
        Schema::dropIfExists('sunat_stock');
    }
}

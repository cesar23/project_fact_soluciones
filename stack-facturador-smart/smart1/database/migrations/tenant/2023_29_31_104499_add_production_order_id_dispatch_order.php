<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductionOrderIdDispatchOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::table('dispatch_orders',function (Blueprint $table){
        
           $table->unsignedInteger('production_order_id')->nullable()->after('establishment_id');
           $table->foreign('production_order_id')->references('id')->on('production_orders')->onDelete('cascade');
       });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('dispatch_orders',function (Blueprint $table){
              $table->dropForeign(['production_order_id']);
              $table->dropColumn('production_order_id');
         });
    }
}

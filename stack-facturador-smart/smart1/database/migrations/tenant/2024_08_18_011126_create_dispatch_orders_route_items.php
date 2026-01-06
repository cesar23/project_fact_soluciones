<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDispatchOrdersRouteItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dispatch_orders_route_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('dispatch_order_route_id');
            $table->unsignedInteger('dispatch_order_id');
            $table->tinyInteger('order');
            $table->foreign('dispatch_order_route_id')->references('id')->on('dispatch_orders_route');
            $table->foreign('dispatch_order_id')->references('id')->on('dispatch_orders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::dropIfExists('dispatch_orders_route_items');
    
    }
}

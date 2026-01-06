<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDispatchOrderDispatch extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::table('dispatches',function (Blueprint $table){
           $table->unsignedInteger('reference_dispatch_order_id')->nullable()->after('reference_document_id');
           $table->foreign('reference_dispatch_order_id')->references('id')->on('dispatch_orders')->onDelete('cascade');
       });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('dispatches',function (Blueprint $table){
              $table->dropForeign(['reference_dispatch_order_id']);
              $table->dropColumn('reference_dispatch_order_id');
         });
    }
}

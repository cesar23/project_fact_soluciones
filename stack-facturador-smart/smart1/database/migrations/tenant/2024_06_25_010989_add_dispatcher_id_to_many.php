<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDispatcherIdToMany extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->unsignedInteger('dispatcher_id')->nullable();
        });

        Schema::table('sale_notes', function (Blueprint $table) {
            $table->unsignedInteger('dispatcher_id')->nullable();
        });

        Schema::table('order_notes', function (Blueprint $table) {
            $table->unsignedInteger('dispatcher_id')->nullable();
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->unsignedInteger('dispatcher_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('dispatcher_id');
        });

        Schema::table('sale_notes', function (Blueprint $table) {
            $table->dropColumn('dispatcher_id');
        });

        Schema::table('order_notes', function (Blueprint $table) {
            $table->dropColumn('dispatcher_id');
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn('dispatcher_id');
        });
    
    
    }
}

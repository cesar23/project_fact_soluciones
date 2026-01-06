<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_05_20_204268_add_location_to_items
class AddLocationToItems extends Migration
{
    public function up()
    {
        
        Schema::table('items', function (Blueprint $table) {
            $table->string('location')->nullable();
        });

    }

    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('location');
        });
    }

}
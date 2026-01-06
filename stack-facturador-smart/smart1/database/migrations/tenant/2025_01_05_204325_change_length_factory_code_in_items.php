<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204325_change_length_factory_code_in_items
class ChangeLengthFactoryCodeInItems extends Migration
{
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('factory_code', 700)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('factory_code', 255)->nullable()->change();
        });
    }
}

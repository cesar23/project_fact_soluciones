<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLockedItemsToClients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->boolean('locked_items')->default(false)->after('locked');
        });
        Schema::table('plans', function (Blueprint $table) {
            $table->bigInteger('limit_items')->default(0)->after('limit_documents');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('locked_items');
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('limit_items');
        });
    }
}

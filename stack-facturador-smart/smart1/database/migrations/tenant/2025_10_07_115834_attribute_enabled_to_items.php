<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AttributeEnabledToItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('items', 'attribute_enabled')) {
            Schema::table('items', function (Blueprint $table) {
                $table->boolean('attribute_enabled')->default(false)->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *p
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('items', 'attribute_enabled')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropColumn('attribute_enabled');
            });
        }
    }
}

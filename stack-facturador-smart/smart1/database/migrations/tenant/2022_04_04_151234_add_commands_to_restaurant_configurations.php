<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCommandsToRestaurantConfigurations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('restaurant_configurations', 'menu_bar')) {
            Schema::table('restaurant_configurations', function (Blueprint $table) {
                $table->boolean('menu_bar')->default(1);
                $table->boolean('menu_kitchen')->default(1);
            });
        }
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('restaurant_configurations', 'menu_bar')) {
            Schema::table('restaurant_configurations', function (Blueprint $table) {
                $table->dropColumn('menu_bar');
                $table->dropColumn('menu_kitchen');
            });
        }
        
    }
}

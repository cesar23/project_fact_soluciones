<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//2025_01_05_204292_add_show_categories_model_brand
class AddShowCategoriesModelBrand extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::table('configurations', function (Blueprint $table) {
            $table->unsignedInteger('show_categories')->nullable();
            $table->unsignedInteger('show_models')->nullable();
            $table->unsignedInteger('show_brands')->nullable();
        });
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('show_categories');
            $table->dropColumn('show_models');
            $table->dropColumn('show_brands');
        });
      
    }
}

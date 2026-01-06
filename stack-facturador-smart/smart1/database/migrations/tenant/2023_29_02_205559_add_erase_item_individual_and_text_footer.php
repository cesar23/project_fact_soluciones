<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEraseItemIndividualAndTextFooter extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->boolean('erase_item_indivual')->default(false);
            
        });
        Schema::table('companies', function (Blueprint $table) {
            
            $table->string('footer_text_template')->nullable();
        });
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('erase_item_indivual');
        });
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('footer_text_template');
        });
    }
}

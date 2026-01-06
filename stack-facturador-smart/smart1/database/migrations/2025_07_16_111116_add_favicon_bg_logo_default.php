<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

//2025_07_16_111116_add_favicon_bg_logo_default


class AddFaviconBgLogoDefault extends Migration
{
    /**
     * Run the migrations.




     *
     * @return void
     */

    public function up()
    {
    
        Schema::table('configurations', function (Blueprint $table) {
            $table->string('tenant_favicon')->nullable();
            $table->string('tenant_bg_logo')->nullable();
            $table->string('tenant_logo')->nullable();
        });



    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('configuration', function (Blueprint $table) {
            $table->dropColumn('tenant_favicon');
            $table->dropColumn('tenant_bg_logo');
            $table->dropColumn('tenant_logo');
        });
    }
}

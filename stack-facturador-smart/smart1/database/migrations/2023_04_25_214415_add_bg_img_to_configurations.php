<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBgImgToConfigurations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasColumn('configurations', 'bg_image')) {
            Schema::table('configurations', function (Blueprint $table) {
                $table->string('bg_image')->nullable()->after('certificate');
            });
        }

        if (!Schema::hasColumn('configurations', 'logo')) {
            Schema::table('configurations', function (Blueprint $table) {
                $table->string('logo')->nullable()->after('certificate');
            });
        }

        if (!Schema::hasColumn('configurations', 'whatsapp')) {
            Schema::table('configurations', function (Blueprint $table) {
                $table->string('whatsapp')->nullable()->after('certificate');
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
        Schema::table('configurations', function (Blueprint $table) {
            //
        });
    }
};

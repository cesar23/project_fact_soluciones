<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


//2025_01_05_204279_add_video_url_item

class AddVideoUrlItem extends Migration
{
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('video_url', 350)->nullable();
        });
    


    }

    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
                $table->dropColumn('video_url');
        });
    }


}
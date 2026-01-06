<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


//2025_05_21_204264_add_to_config_detraction_transport_not_require_fields
class AddToConfigDetractionTransportNotRequireFields extends Migration
{
    public function up()
    {
    

        Schema::table('configurations', function (Blueprint $table) {
            $table->boolean('detraction_transport_not_require_fields')->default(false);
        });
    }

    public function down()
    {
        Schema::table('configurations', function (Blueprint $table) {
            $table->dropColumn('detraction_transport_not_require_fields');
        });
    }
}
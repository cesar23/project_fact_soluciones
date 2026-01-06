<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_01_05_204339_add_fields_technical_services
class AddFieldsTechnicalServices0 extends Migration
{
    public function up()
    {
        Schema::table('technical_services', function (Blueprint $table) {
            if (!Schema::hasColumn('technical_services', 'web_platform_id')) {
                $table->unsignedInteger('web_platform_id')->nullable();
                $table->foreign('web_platform_id')->references('id')->on('web_platforms');
            }
            if (!Schema::hasColumn('technical_services', 'supplier_id')) {
                $table->unsignedInteger('supplier_id')->nullable();
            }
            if (!Schema::hasColumn('technical_services', 'purchase_order')) {
                $table->string('purchase_order')->nullable();
                $table->foreign('supplier_id')->references('id')->on('persons');
            }
        });
    }

    public function down()
    {
        Schema::table('technical_services', function (Blueprint $table) {
            if (Schema::hasColumn('technical_services', 'web_platform_id')) {
                $table->dropColumn('web_platform_id');
            }
            if (Schema::hasColumn('technical_services', 'supplier_id')) {
                $table->dropColumn('supplier_id'); 
            }
            if (Schema::hasColumn('technical_services', 'purchase_order')) {
                $table->dropColumn('purchase_order');
            }
        });
    }
}

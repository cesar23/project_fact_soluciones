<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebsiteIdInCashPurchases extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('cash', 'website_id')) {
            Schema::table('cash', function (Blueprint $table) {
                $table->unsignedInteger('website_id')->nullable()->after('id');
                $table->string('company')->nullable()->after('website_id');
            });
        }

        if (!Schema::hasColumn('purchases', 'website_id')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->unsignedInteger('website_id')->nullable()->after('id');
                $table->string('company')->nullable()->after('website_id');
            });
        }

    }

    public function down()
    {
        Schema::table('cash', function (Blueprint $table) {
            $table->dropColumn('company');
            $table->dropColumn('website_id');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('company');
            $table->dropColumn('website_id');
        });
        
    }
}
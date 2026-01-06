<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebsiteIdInDocuments extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('documents', 'website_id')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->unsignedInteger('website_id')->nullable()->after('id');
                $table->string('company')->nullable()->after('website_id');
            });
        }

        if (!Schema::hasColumn('sale_notes', 'website_id')) {
            Schema::table('sale_notes', function (Blueprint $table) {
                $table->unsignedInteger('website_id')->nullable()->after('id');
                $table->string('company')->nullable()->after('website_id');
            });
        }

        if (!Schema::hasColumn('quotations', 'website_id')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->unsignedInteger('website_id')->nullable()->after('id');
                $table->string('company')->nullable()->after('website_id');
            });
        }

        if (!Schema::hasColumn('dispatches', 'website_id')) {
            Schema::table('dispatches', function (Blueprint $table) {
                $table->unsignedInteger('website_id')->nullable()->after('id');
                $table->string('company')->nullable()->after('website_id');
            });
        }

        if (!Schema::hasColumn('order_notes', 'website_id')) {
            Schema::table('order_notes', function (Blueprint $table) {
                $table->unsignedInteger('website_id')->nullable()->after('id');
                $table->string('company')->nullable()->after('website_id');
            });
        }
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('company');
            $table->dropColumn('website_id');
        });

        Schema::table('sale_notes', function (Blueprint $table) {
            $table->dropColumn('company');
            $table->dropColumn('website_id');
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn('company');
            $table->dropColumn('website_id');
        });

        Schema::table('dispatches', function (Blueprint $table) {
            $table->dropColumn('company');
            $table->dropColumn('website_id');
        });

        Schema::table('order_notes', function (Blueprint $table) {
            $table->dropColumn('company');
            $table->dropColumn('website_id');
        });
    }
}
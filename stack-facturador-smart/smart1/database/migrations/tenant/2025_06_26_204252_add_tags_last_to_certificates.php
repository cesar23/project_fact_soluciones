<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//2025_06_26_204252_add_tags_last_to_certificates
class AddTagsLastToCertificates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->text('tag_8')->nullable();
            $table->text('tag_9')->nullable();
        });

        Schema::table('certificates_person', function (Blueprint $table) {
            $table->text('tag_8')->nullable();
            $table->text('tag_9')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn('tag_8');
            $table->dropColumn('tag_9');
        });

        Schema::table('certificates_person', function (Blueprint $table) {
            $table->dropColumn('tag_8');
            $table->dropColumn('tag_9');
        });
    }
}

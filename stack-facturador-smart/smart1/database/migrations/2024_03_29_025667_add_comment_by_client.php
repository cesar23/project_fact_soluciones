<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCommentByClient extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("clients", function (Blueprint $table) {
            $table->text('comment')->after('name')->nullable();
            $table->string('telephone')->after('name')->nullable();
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropColumn('comment');
                $table->dropColumn('telephone');
            });
    }
}

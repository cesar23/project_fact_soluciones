<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsAnulateHistoryAuditor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('auditor_history', function (Blueprint $table) {
            
            $table->boolean('is_anulate')->default(false)->after('is_recreate');

            // $table->string('st

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('auditor_history', function (Blueprint $table) {
            $table->dropColumn('is_anulate');
        });
    
    }
}

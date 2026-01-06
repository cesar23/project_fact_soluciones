<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEditRecreateAuditorHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('auditor_history', function (Blueprint $table) {
            
            $table->boolean('is_edit')->default(false)->after('old_state_type_id');
            $table->boolean('is_recreate')->default(false)->after('is_edit');

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
            $table->dropColumn('is_edit');
            $table->dropColumn('is_recreate');
        });
    
    }
}

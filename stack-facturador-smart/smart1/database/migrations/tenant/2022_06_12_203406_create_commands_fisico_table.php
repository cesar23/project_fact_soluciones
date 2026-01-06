<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommandsFisicoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('configurations', 'commands_fisico')) {
            Schema::table('configurations', function (Blueprint $table) {
                $table->boolean('commands_fisico')->default(false)->nullable();
            });
        }

        if (!Schema::hasColumn('ordens', 'commands_fisico')) {
            Schema::table('ordens', function (Blueprint $table) {
                $table->string('commands_fisico', 30)->default("")->nullable();
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
        if (Schema::hasColumn('configurations', 'commands_fisico')) {
            Schema::table('configurations', function (Blueprint $table) {
                $table->dropColumn('commands_fisico');
            });
        }

        if (Schema::hasColumn('ordens', 'commands_fisico')) {

            Schema::table('ordens', function (Blueprint $table) {
                $table->dropColumn('commands_fisico');
            });
        }
    }
}

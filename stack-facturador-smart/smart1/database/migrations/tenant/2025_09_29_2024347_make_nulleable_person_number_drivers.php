<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
//2025_09_29_2024347_make_nulleable_person_number_drivers
class MakeNulleablePersonNumberDrivers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('drivers', 'person_number')) {
            Schema::table('drivers', function (Blueprint $table) {
                $table->string('person_number')->nullable()->change();
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
        if (Schema::hasColumn('drivers', 'person_number')) {
            Schema::table('drivers', function (Blueprint $table) {
                $table->string('person_number')->nullable()->change();
            });
        }
    }
}

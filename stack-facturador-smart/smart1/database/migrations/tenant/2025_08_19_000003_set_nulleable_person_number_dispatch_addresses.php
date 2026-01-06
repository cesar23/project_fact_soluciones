<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_08_19_000006_set_nulleable_person_number_dispatch_addresses
class SetNulleablePersonNumberDispatchAddresses extends Migration       
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('dispatch_addresses', 'person_number')) {
            Schema::table('dispatch_addresses', function (Blueprint $table) {
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
        if (Schema::hasColumn('dispatch_addresses', 'person_number')) {
            Schema::table('dispatch_addresses', function (Blueprint $table) {
                $table->string('person_number')->nullable(false)->change();
            });
        }
    }
}

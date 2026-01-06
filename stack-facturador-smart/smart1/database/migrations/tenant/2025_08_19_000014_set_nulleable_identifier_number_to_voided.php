<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_08_19_000014_set_nulleable_identifier_number_to_voided
class SetNulleableIdentifierNumberToVoided extends Migration      
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('voided', 'identifier_number')) {
            Schema::table('voided', function (Blueprint $table) {
                $table->smallInteger('identifier_number')->nullable()->change();
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
        
    }
}

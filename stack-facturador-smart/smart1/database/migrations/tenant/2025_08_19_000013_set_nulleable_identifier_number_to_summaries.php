<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_08_19_000013_set_nulleable_identifier_number_to_summaries
class SetNulleableIdentifierNumberToSummaries extends Migration      
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('summaries', 'identifier_number')) {
            Schema::table('summaries', function (Blueprint $table) {
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_08_19_000014_add_description_to_quotations_qpos
class AddDescriptionToQuotationsQpos extends Migration      
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('quotations', 'description')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->string('description', 255)->nullable();
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
        if (Schema::hasColumn('quotations', 'description')) {   
            Schema::table('quotations', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
    }
}

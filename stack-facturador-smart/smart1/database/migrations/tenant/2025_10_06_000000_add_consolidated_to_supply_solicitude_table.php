<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConsolidatedToSupplySolicitudeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('supply_solicitude', function (Blueprint $table) {
            $table->boolean('consolidated')->default(false)->after('observation');
            $table->timestamp('consolidated_at')->nullable()->after('consolidated');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('supply_solicitude', function (Blueprint $table) {
            $table->dropColumn(['consolidated', 'consolidated_at']);
        });
    }
}

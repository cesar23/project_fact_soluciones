<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantVoidedRetentionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('voided_retentions')) {
            Schema::create('voided_retentions', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('voided_id');
                $table->unsignedInteger('retention_id');
                $table->string('description');

                $table->foreign('voided_id')->references('id')->on('voided')->onDelete('cascade');
                $table->foreign('retention_id')->references('id')->on('retentions');
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
        Schema::dropIfExists('voided_retentions');
    }
}

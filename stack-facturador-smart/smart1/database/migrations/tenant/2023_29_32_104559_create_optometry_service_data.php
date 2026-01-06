<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOptometryServiceData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('optometry_service_data', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('optometry_service_id');
            $table->text('od_av_sc')->nullable();
            $table->text('od_av_cc')->nullable();
            $table->text('od_dnp')->nullable();
            $table->text('od_kx')->nullable();
            $table->text('oi_av_sc')->nullable();
            $table->text('oi_av_cc')->nullable();
            $table->text('oi_dnp')->nullable();
            $table->text('oi_kx')->nullable();
            $table->boolean('cover_test')->default(false);
            $table->boolean('ppc')->default(false);
            $table->boolean('motilidad')->default(false);
            $table->boolean('reaccion_pupilar')->default(false);
            $table->text('od_esf')->nullable();
            $table->text('od_cil')->nullable();
            $table->text('od_eje')->nullable();
            $table->text('od_ad')->nullable();
            $table->text('od_av')->nullable();
            $table->text('oi_esf')->nullable();
            $table->text('oi_cil')->nullable();
            $table->text('oi_eje')->nullable();
            $table->text('oi_ad')->nullable();
            $table->text('oi_av')->nullable();
            $table->boolean('test_worth')->default(false);
            $table->text('test_worth_description')->nullable();
            $table->boolean('test_schober')->default(false);
            $table->text('test_schober_description')->nullable();
            $table->boolean('test_color')->default(false);
            $table->text('test_color_description')->nullable();
            $table->boolean('amster')->default(false);
            $table->text('amster_description')->nullable();
            $table->boolean('test_medias')->default(false);
            $table->text('test_medias_description')->nullable();
            $table->boolean('retina')->default(false);
            $table->text('retina_description')->nullable();
            $table->boolean('pio')->default(false);
            $table->text('pio_description')->nullable();
            $table->foreign('optometry_service_id')->references('id')->on('optometry_services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('optometry_service_data');
    }
}

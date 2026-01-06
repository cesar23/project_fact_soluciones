<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeFieldsNullableInCouponsTable extends Migration
{
    public function up()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->string('nombre')->nullable()->change();
            $table->string('titulo')->nullable()->change();
            $table->text('descripcion')->nullable()->change();
            $table->decimal('descuento', 8, 2)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->string('nombre')->nullable(false)->change();
            $table->string('titulo')->nullable(false)->change();
            $table->text('descripcion')->nullable(false)->change();
            $table->decimal('descuento', 8, 2)->nullable(false)->change();
        });
    }
}

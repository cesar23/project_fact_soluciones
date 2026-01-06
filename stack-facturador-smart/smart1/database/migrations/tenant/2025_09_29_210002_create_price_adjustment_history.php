<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_09_29_210002_create_price_adjustment_history.php

class CreatePriceAdjustmentHistory extends Migration
{
    public function up()
    {
        Schema::create('price_adjustment_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('price_adjustment_id');
            $table->timestamp('applied_at');
            $table->unsignedInteger('applied_by')->nullable();
            $table->integer('items_affected')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('price_adjustment_id')->references('id')->on('price_adjustments')->onDelete('cascade');
            $table->foreign('applied_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('price_adjustment_history');
    }
}
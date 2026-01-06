<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

//2025_09_29_210000_create_price_adjustments.php

class CreatePriceAdjustments extends Migration
{
    public function up()
    {
        Schema::create('price_adjustments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('description');
            $table->enum('apply_to', ['all', 'category', 'brand', 'specific'])->default('all');
            $table->enum('adjustment_type', ['percentage', 'amount'])->default('percentage');
            $table->enum('operation', ['increase', 'decrease'])->default('increase');
            $table->decimal('value', 12, 2);
            $table->boolean('applied')->default(false);
            $table->timestamp('applied_at')->nullable();
            $table->unsignedInteger('applied_by')->nullable();
            $table->integer('items_affected')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('applied_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('price_adjustments');
    }
}
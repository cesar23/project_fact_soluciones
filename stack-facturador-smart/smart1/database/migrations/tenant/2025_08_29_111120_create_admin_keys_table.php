<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_keys', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('admin_id');
            $table->string('key_code', 8);
            $table->boolean('is_active')->default(true);
            $table->datetime('expires_at')->nullable();
            $table->integer('max_uses')->nullable();
            $table->integer('current_uses')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();

            // Índices
            $table->index('admin_id');
            $table->index('is_active');

            // Foreign key constraint
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');

            // Constraint: Solo una clave activa por admin (se manejará a nivel de aplicación)
            // O se puede usar un índice parcial en bases de datos que lo soporten
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_keys');
    }
};
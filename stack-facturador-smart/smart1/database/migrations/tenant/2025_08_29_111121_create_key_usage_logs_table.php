<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKeyUsageLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('key_usage_logs')) {
            Schema::create('key_usage_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('seller_id');
                $table->unsignedBigInteger('admin_key_id');
                $table->unsignedInteger('document_id')->nullable();
                $table->string('operation_type', 50);
                $table->timestamp('created_at')->useCurrent();

                // Índices
                $table->index('seller_id');
                $table->index('admin_key_id');
                $table->index('operation_type');

                // Foreign key constraints
                $table->foreign('seller_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('admin_key_id')->references('id')->on('admin_keys')->onDelete('cascade');
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
        if (Schema::hasTable('key_usage_logs')) {
            Schema::dropIfExists('key_usage_logs');
        }
    }
};
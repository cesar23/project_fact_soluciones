<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
//2025_10_08_163955_create_quick_access_table
class CreateQuickAccessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quick_access', function (Blueprint $table) {
            $table->increments('id');
            $table->string('icons')->nullable()->default('');
            $table->string('link');
            $table->string('description');
            $table->timestamps();
        });

        DB::connection('tenant')->table('quick_access')->insert([
            ['icons' => 'bi bi-file-earmark-text-fill', 'description' => 'FACTURAS', 'link' => 'documents/create'],
            ['icons' => 'bi bi-file-earmark-text-fill', 'description' => 'NOTAS VENTA', 'link' => 'sale-notes'],
            ['icons' => 'bi bi-basket-fill', 'description' => 'POS', 'link' => 'pos'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quick_access');
    }
}

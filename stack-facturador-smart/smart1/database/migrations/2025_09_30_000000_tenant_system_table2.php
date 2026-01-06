<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantSystemTable2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // state_types
        if(!Schema::hasTable('state_types')) {
            Schema::create('state_types', function (Blueprint $table) {
                $table->char('id', 2)->index();
                $table->string('description');
            });
        }
        if(DB::table('state_types')->count() == 0) {
            DB::table('state_types')->insert([
                ['id' => '01', 'description' => 'Registrado'],
                ['id' => '03', 'description' => 'Enviado'],
                ['id' => '05', 'description' => 'Aceptado'],
                ['id' => '07', 'description' => 'Observado'],
                ['id' => '09', 'description' => 'Rechazado'],
                ['id' => '11', 'description' => 'Anulado'],
                ['id' => '13', 'description' => 'Por anular'],
            ]);
        }

        // soap_types
        if(!Schema::hasTable('soap_types')) {
            Schema::create('soap_types', function (Blueprint $table) {
                $table->char('id', 2)->index();
                $table->string('description');
            });
        }
        if(DB::table('soap_types')->count() == 0) {
            DB::table('soap_types')->insert([
                ['id' => '01', 'description' => 'Demo'],
                ['id' => '02', 'description' => 'Producción'],
            ]);
        }

        // groups
        if(!Schema::hasTable('groups')) {
            Schema::create('groups', function (Blueprint $table) {
                $table->char('id', 2)->index();
                $table->string('description');
            });
        }
        if(DB::table('groups')->count() == 0) {
            DB::table('groups')->insert([
                ['id' => '01', 'description' => 'Facturas'],
                ['id' => '02', 'description' => 'Boletas'],
            ]);
        }

        // item_types
        if(!Schema::hasTable('item_types')) {
            Schema::create('item_types', function (Blueprint $table) {
                $table->char('id', 2)->index();
                $table->string('description');
            });
        }
        if(DB::table('item_types')->count() == 0) {
            DB::table('item_types')->insert([
                ['id' => '01', 'description' => 'Producto'],
                ['id' => '02', 'description' => 'Servicio']
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('bank_accounts');
        Schema::dropIfExists('banks');
        Schema::dropIfExists('item_types');
        Schema::dropIfExists('groups');
        Schema::dropIfExists('soap_types');
        Schema::dropIfExists('process_types');
        Schema::dropIfExists('state_types');
    }
}

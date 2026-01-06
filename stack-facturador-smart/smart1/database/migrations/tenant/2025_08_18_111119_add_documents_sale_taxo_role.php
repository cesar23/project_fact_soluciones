<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;



class AddDocumentsSaleTaxoRole extends Migration
{
    /**
     * Run the migrations.




     *
     * @return void
     */

    public function up()
    {

        $table = DB::connection('tenant')->table('app_configuration_taxo_role');
        $getIds = DB::connection('tenant')->table('app_configuration_taxo')->whereIn('route', ['sale-invoice', 'sale-bill', 'sale-note', 'sale-quotation', 'sale-order-note'])->get()->pluck('id');
        $types = ['admin', 'seller', 'integrator', 'client', 'superadmin'];
        $now = now();
        foreach ($types as $type) {
            foreach ($getIds as $id) {
                $table->insert([
                    'app_configuration_taxo_id' => $id,
                    'role_id' => $type,
                    'is_visible' => true,
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
            }
        }
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection('tenant')->table('app_configuration_taxo_role')->whereIn('app_configuration_taxo_id', DB::connection('tenant')->table('app_configuration_taxo')->whereIn('route', ['sale-invoice', 'sale-bill', 'sale-note', 'sale-quotation', 'sale-order-note'])->get()->pluck('id'))->delete();
    }
}

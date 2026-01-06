<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
//2024_08_18_011185_pse_check_dem_pass_prod

class PseCheckDemPassProd extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::connection('tenant')->table('companies')
            ->where('pse', 1)
            ->update([
                'soap_send_id' => '02',
                'soap_type_id' => '02'
            ]);
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        
    }
}

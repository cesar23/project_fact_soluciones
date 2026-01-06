<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeUrlSoapSmart extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $company = DB::connection('tenant')->table('companies')->first();
        //si existe soap_url y es igual a la url de smart

        if ($company) {
            if ($company->soap_url && $company->soap_url == "https://prod.conose.cpe.pe/ol-ti-itcpe/billService?wsdl") {
                DB::connection('tenant')->table('companies')->update(['soap_url' => 'https://ose.cpe.pe/ol-ti-itcpe/billService?wsdl']);
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
    }
}

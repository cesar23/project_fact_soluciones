<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class DiscountAjustmentByIgvAffectation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $connection_tenant = DB::connection('tenant');
        $configurations = $connection_tenant->table('configurations')->select('affectation_igv_type_id', 'global_discount_type_id')->first();
        if(!$configurations){
            return;
        }
        $affectation_igv_type_id = $configurations->affectation_igv_type_id;
        $global_discount_type_id = $configurations->global_discount_type_id;
        if($affectation_igv_type_id == "10"){
            $global_discount_type_id = "02";
        }else{
            $global_discount_type_id = "03";
        }
        $connection_tenant->table('configurations')->where('id', 1)->update(['global_discount_type_id' => $global_discount_type_id]);
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

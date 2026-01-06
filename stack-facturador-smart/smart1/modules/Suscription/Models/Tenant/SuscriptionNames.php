<?php


namespace Modules\Suscription\Models\Tenant;

use App\Models\Tenant\Document;
use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\SaleNote;
use Hyn\Tenancy\Traits\UsesTenantConnection;

class SuscriptionNames extends ModelTenant
{

    use UsesTenantConnection;
    protected $table = 'name_suscription';
    public $timestamps = false;

    protected $fillable = [
        'parents',
        'children',
        'sections',
        'grades',
    ];


    public static function create_new(){
        $suscription = SuscriptionNames::first();
        if($suscription){
            return $suscription;
        }

        $suscription = new SuscriptionNames();
        $suscription->parents = "Padre";
        $suscription->children = "Hijo";
        $suscription->sections = "Secciones";
        $suscription->grades = "Grados";
        $suscription->save();
        return $suscription;
    }
}

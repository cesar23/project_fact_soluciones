<?php


namespace Modules\Optometry\Models;

use App\Models\Tenant\ModelTenant;
use App\Traits\AttributePerItems;
use Hyn\Tenancy\Traits\UsesTenantConnection;


class OptometryServiceData extends ModelTenant
{
    use AttributePerItems;
    use UsesTenantConnection;

    public $timestamps = false;

    protected $fillable = [
        "optometry_service_id",
        "od_av_sc",
        "od_av_cc",
        "od_dnp",
        "od_kx",
        "oi_av_sc",
        "oi_av_cc",
        "oi_dnp",
        "oi_kx",
        "cover_test",
        "ppc",
        "motilidad",
        "reaccion_pupilar",
        "od_esf",
        "od_cil",
        "od_eje",
        "od_ad",
        "od_av",
        "oi_esf",
        "oi_cil",
        "oi_eje",
        "oi_ad",
        "oi_av",
        "test_worth",
        "test_worth_description",
        "test_schober",
        "test_schober_description",
        "test_color",
        "test_color_description",
        "amster",
        "amster_description",
        "test_medias",
        "test_medias_description",
        "retina",
        "retina_description",
        "pio",
        "pio_description",
    ];

    public function optometryService()
    {
        return $this->belongsTo(OptometryService::class);
    }
}

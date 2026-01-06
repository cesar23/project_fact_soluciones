<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Catalogs\AffectationIgvType;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use App\Models\Tenant\Catalogs\SystemIscType;
use App\Models\Tenant\Catalogs\PriceType;

class DispatchOrderItem extends ModelTenant
{
    use UsesTenantConnection;
    
    public $timestamps = false;
    protected $with = ['affectation_igv_type', 'system_isc_type', 'price_type'];

    protected $fillable = [
        "dispatch_order_id",
        "item_id",
        "item",
        "quantity",
        "unit_value",
        "affectation_igv_type_id",
        "total_base_igv",
        "percentage_igv",
        "total_igv",
        "system_isc_type_id",
        "total_base_isc",
        "percentage_isc",
        "total_isc",
        "total_base_other_taxes",
        "percentage_other_taxes",
        "total_other_taxes",
        "total_plastic_bag_taxes",
        "total_taxes",
        "price_type_id",
        "unit_price",
        "total_value",
        "total_charge",
        "total_discount",
        "total",
        "attributes",
        "discounts",
        "charges",
        "additional_information",
        "warehouse_id",
        "name_product_pdf",
    ];

    protected $casts = [];
    
    public function production_order()
    {
        return $this->belongsTo(ProductionOrder::class);
    }
    public function affectation_igv_type()
    {
        return $this->belongsTo(AffectationIgvType::class, 'affectation_igv_type_id');
    }
    public function getItemAttribute($value)
    {
        return (is_null($value)) ? null : (object) json_decode($value);
    }
    public function system_isc_type()
    {
        return $this->belongsTo(SystemIscType::class, 'system_isc_type_id');
    }

    public function price_type()
    {
        return $this->belongsTo(PriceType::class, 'price_type_id');
    }
    public function setItemAttribute($value)
    {
        $this->attributes['item'] = (is_null($value)) ? null : json_encode($value);
    }

    public function getAttributesAttribute($value)
    {
        return (is_null($value)) ? null : (object) json_decode($value);
    }
    public function getExchangeRateSale()
    {
        return $this->production_order->exchange_rate_sale;
    }
    public function setAttributesAttribute($value)
    {
        $this->attributes['attributes'] = (is_null($value)) ? null : json_encode($value);
    }

    public function getChargesAttribute($value)
    {
        return (is_null($value)) ? null : (object) json_decode($value);
    }

    public function setChargesAttribute($value)
    {
        $this->attributes['charges'] = (is_null($value)) ? null : json_encode($value);
    }

    public function getDiscountsAttribute($value)
    {
        return (is_null($value)) ? null : (object) json_decode($value);
    }

    public function setDiscountsAttribute($value)
    {
        $this->attributes['discounts'] = (is_null($value)) ? null : json_encode($value);
    }

}

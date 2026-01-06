<?php

namespace App\Models\Tenant;


class PackageHandler extends ModelTenant
{
    protected $table = 'package_handlers';
    protected $fillable = [
        "id",
        "establishment",
        "observation",
        "time_of_issue",
        "series",
        "number",
        "establishment_id",
        "user_id",
        "seller_id",
        "driver_id",
        "driver",
        "cash_id",
        "sender_id",
        "sender",
        "issuer_id",
        "issuer",
        "date_of_issue",
        "license_plate",
        "departure",
        "arrival",
        "currency_type_id",
        "exchange_rate_sale",
        "total_prepayment",
        "total_charge",
        "total_discount",
        "total_exportation",
        "total_free",
        "total_taxed",
        "total_unaffected",
        "total_exonerated",
        "total_igv",
        "total_base_isc",
        "total_isc",
        "total_base_other_taxes",
        "total_other_taxes",
        "total_taxes",
        "total_value",
        "total",
    ];

    protected $casts = [
        'total' => 'float',
        'total_prepayment' => 'float',
        'total_charge' => 'float',
        'total_discount' => 'float',
        'total_exportation' => 'float',
        'total_free' => 'float',
        'total_taxed' => 'float',
        'total_unaffected' => 'float',
        'total_exonerated' => 'float',
        'total_igv' => 'float',
        'total_base_isc' => 'float',
        'total_isc' => 'float',
        'total_base_other_taxes' => 'float',
        'total_other_taxes' => 'float',
        'total_taxes' => 'float',
        'total_value' => 'float',
        'exchange_rate_sale' => 'float',
        
        // 'establishment' => 'array',
        // 'driver' => 'array',
        // 'sender' => 'array',
        // 'issuer' => 'array'
    ];
    
    public function getEstablishmentAttribute($value)
    {
        return (is_null($value)) ? null : (object)json_decode($value);
    }

    public function setEstablishmentAttribute($value)
    {
        $this->attributes['establishment'] = (is_null($value)) ? null : json_encode($value);
    }
    public function getSenderAttribute($value)
    {
        
        return (is_null($value)) ? null : (object)json_decode($value);
    }
    
    public function setSenderAttribute($value)
    {
        $this->attributes['sender'] = (is_null($value)) ? null : json_encode($value);
    }
    public function getIssuerAttribute($value)
    {
        return (is_null($value)) ? null : (object)json_decode($value);
    }
    
    public function setIssuerAttribute($value)
    {
        $this->attributes['issuer'] = (is_null($value)) ? null : json_encode($value);
    }
    public function getDriverAttribute($value)
    {
        return (is_null($value)) ? null : (object)json_decode($value);
    }
    
    public function setDriverAttribute($value)
    {
        $this->attributes['driver'] = (is_null($value)) ? null : json_encode($value);
    }
    
    public function payments()
    {
        return $this->hasMany(PackageHandlerPayment::class);
    }
    public function items()
    {
        return $this->hasMany(PackageHandlerItem::class);
    }

    public function issuer()
    {
        return $this->belongsTo(Person::class, 'issuer_id');
    }
    public function sender()
    {
        return $this->belongsTo(Person::class, 'sender_id');
    }
    public function driver()
    {
        return $this->belongsTo(Person::class, 'driver_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

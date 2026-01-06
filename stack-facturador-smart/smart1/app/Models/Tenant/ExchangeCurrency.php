<?php

namespace App\Models\Tenant;

use Hyn\Tenancy\Traits\UsesTenantConnection;

class ExchangeCurrency extends ModelTenant
{
    use UsesTenantConnection;


    protected $table = "exchange_rate_currencies";
    public $timestamps = false;

    protected $fillable = [
        'id',
        'currency_id',
        'date',
        'sale',
        'purchase',
    ];


    public function currency()
    {
        return $this->belongsTo(Catalogs\CurrencyType::class, 'currency_id');
    }
}

<?php

namespace App\Models\Tenant;

use App\CoreFacturalo\Helpers\Number\NumberLetter;
use App\Models\Tenant\Catalogs\CurrencyType;
use Modules\Finance\Models\PaymentFile;

/**
 * Class DocumentFee
 *
 * @package App\Models\Tenant
 */
class BillOfExchange extends ModelTenant
{
    public $timestamps = true;
    protected $table = 'bills_of_exchange';

    protected $fillable = [
        'id',
        'code',
        'series',
        'number',
        'date_of_due',
        'total',
        'establishment_id',
        'customer_id',
        'user_id',
        'currency_type_id',
        'endorsement_name',
        'endorsement_number',
    ];

    protected $casts = [
        'date_of_due' => 'date',
        'total' => 'float',
    ];

    public function establishment()
    {
        return $this->belongsTo(Establishment::class, 'establishment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
  

    public function items(){
        return $this->hasMany(BillOfExchangeDocument::class);
    }

    public function customer()
    {
        return $this->belongsTo(Person::class, 'customer_id');
    }

    public function person()
    {
        return $this->belongsTo(Person::class, 'customer_id');
    }

    public function payments()
    {
        return $this->hasMany(BillOfExchangePayment::class);
    }
    public function getNumberFullAttribute()
    {
        if ($this->code) {
            return $this->code;
        }
        return $this->series . '-' . $this->number;
    }

    public function currency_type()
    {
        return $this->belongsTo(CurrencyType::class, 'currency_type_id');
    }
    
    public function total_text()
    {
        return NumberLetter::convertToLetter($this->total, $this->currency_type->description);
    }

    public function getAvalByNumberAttribute()
    {
        return $this->customer->person_aval;
    }
}

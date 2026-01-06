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
class BillOfExchangePay extends ModelTenant
{
    public $timestamps = false;
    protected $table = 'bills_of_exchange_pay';

    protected $fillable = [
        'id',
        'series',
        'number',
        'date_of_due',
        'total',
        'establishment_id',
        'supplier_id',
        'user_id',
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



    public function items()
    {
        return $this->hasMany(BillOfExchangeDocumentPay::class,'bill_of_exchange_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Person::class, 'supplier_id');
    }

    public function payments()
    {
        return $this->hasMany(
            BillOfExchangePaymentPay::class,
            'bill_of_exchange_id'
        );
    }

    public function documents()
    {
        return $this->hasMany(BillOfExchangeDocumentPay::class, 'bill_of_exchange_id');
    }

    public function currency_type()
    {
        return $this->belongsTo(CurrencyType::class, 'currency_type_id');
    }
    public function total_text()
    {
        return NumberLetter::convertToLetter($this->total, $this->currency_type->description);
    }
}

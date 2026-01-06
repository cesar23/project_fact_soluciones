<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Catalogs\CurrencyType;

/**
 * Class DocumentFee
 *
 * @package App\Models\Tenant
 */
class BillOfExchangeDocumentPay extends ModelTenant
{
    public $timestamps = false;
    protected $table = 'bills_of_exchange_documents_pay';

    protected $fillable = [
        'id',
        'bill_of_exchange_id',
        'purchase_id',
        'total',
    ];

 
    public function document()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function bill_of_exchange()
    {
        return $this->belongsTo(BillOfExchangePay::class, 'bill_of_exchange_id');
    }
}

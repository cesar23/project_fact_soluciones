<?php

namespace App\Models\Tenant;

use Modules\Finance\Models\GlobalPayment;
use Modules\Finance\Models\PaymentFile;

/**
 * Class DocumentFee
 *
 * @package App\Models\Tenant
 */
class BillOfExchangePaymentPay extends ModelTenant
{
    public $timestamps = false;
    protected $table = 'bills_of_exchange_payments_pay';

    protected $fillable = [
        'bill_of_exchange_id',
        'date_of_payment',
        'payment_method_type_id',
        'has_card',
        'card_brand_id',
        'reference',
        'payment',
        'total_canceled',
    ];

    protected $casts = [
        'date_of_payment' => 'date',
        'payment' => 'float',
        'total_canceled' => 'boolean'
    ];

    public function bill_of_exchange()
    {
        return $this->belongsTo(BillOfExchangePay::class, 'bill_of_exchange_id');
    }

    public function payment_method_type()
    {
        return $this->belongsTo(PaymentMethodType::class, 'payment_method_type_id');
    }

    public function card_brand()
    {
        return $this->belongsTo(CardBrand::class, 'card_brand_id');
    }
    public function payment_file(){
        return $this->morphOne(PaymentFile::class, 'payment');
    }
    public function global_payment()
    {
        return $this->morphOne(GlobalPayment::class, 'payment');
    }
    
    public function associated_record_payment()
    {
        return $this->belongsTo(BillOfExchangePay::class, 'bill_of_exchange_id');
    }
}

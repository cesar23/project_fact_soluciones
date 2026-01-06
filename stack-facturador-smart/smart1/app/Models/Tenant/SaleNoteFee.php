<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Catalogs\CurrencyType;

class SaleNoteFee extends ModelTenant
{
    public $timestamps = false;
    protected $table = 'sale_note_fee';

    protected $fillable = [
        'sale_note_id',
        'date',
        'currency_type_id',
        'amount',
        'payment_method_type_id'
    ];

    protected $casts = [
        // 'date' => 'date',
        'amount' => 'float',
    ];

    public function getStringPaymentMethodType(){

        $payment_method_type = PaymentMethodType::where('id',$this->payment_method_type_id)->first();
        $return = null;
        if(!empty($payment_method_type)){
            $return =  $payment_method_type->getDescription();
        }
        return $return;
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sale_note()
    {
        return $this->belongsTo(SaleNote::class, 'sale_note_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency_type()
    {
        return $this->belongsTo(CurrencyType::class, 'currency_type_id');
    }
    
    public function payment_method_type(){
        return $this->belongsTo(PaymentMethodType::class, 'payment_method_type_id');
    }



}

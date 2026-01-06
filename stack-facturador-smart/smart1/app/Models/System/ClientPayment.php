<?php

namespace App\Models\System;
use Hyn\Tenancy\Traits\UsesSystemConnection;
use Illuminate\Database\Eloquent\Model;

class ClientPayment extends Model
{
    protected $with = ['payment_method_type', 'card_brand'];

    protected $fillable = [
        'client_id', 'date_of_payment', 'payment_method_type_id', 'has_card', 'card_brand_id', 'reference', 'payment', 'state', 'photos'

    ];

    protected $casts = [
        'date_of_payment' => 'date',
        'photos' => 'array', // Indicar que photos es un array
    ];

    public function payment_method_type()
    {
        return $this->belongsTo(PaymentMethodType::class);
    }

    public function card_brand()
    {
        return $this->belongsTo(CardBrand::class);
    }
}
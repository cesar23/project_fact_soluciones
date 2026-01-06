<?php

namespace Modules\Finance\Models;

use App\Models\Tenant\{
    BillOfExchangePaymentPay,
    BillOfExchangePayment,
    DocumentPayment,
    SaleNotePayment,
    ModelTenant
};

class PaymentFile extends ModelTenant
{

    public $timestamps = false;

    protected $fillable = [
        'filename',
        'payment_id',
        'payment_type',
    ];


    public function payment()
    {
        return $this->morphTo();
    }

    public function doc_payments()
    {
        return $this->belongsTo(DocumentPayment::class, 'payment_id')
            ->wherePaymentType(DocumentPayment::class);
    }

    public function sln_payments()
    {
        return $this->belongsTo(SaleNotePayment::class, 'payment_id')
            ->wherePaymentType(SaleNotePayment::class);
    }
    public function bill_pay_payments(){
        return $this->belongsTo(BillOfExchangePaymentPay::class, 'payment_id')
            ->wherePaymentType(BillOfExchangePaymentPay::class);
    }
    public function bill_payments()
    {
        return $this->belongsTo(BillOfExchangePayment::class, 'payment_id')
            ->wherePaymentType(BillOfExchangePayment::class);
    }

    public function getInstanceTypeAttribute()
    {
        $instance_type = [
            DocumentPayment::class => 'document',
            SaleNotePayment::class => 'sale_note',
            BillOfExchangePayment::class => 'bill_of_exchange',
            BillOfExchangePaymentPay::class => 'bill_of_exchange_pay',
        ];

        return $instance_type[$this->payment_type];
    }

    public function getInstanceTypeDescriptionAttribute()
    {

        $description = null;

        switch ($this->instance_type) {
            case 'document':
                $description = 'CPE';
                break;
            case 'sale_note':
                $description = 'NOTA DE VENTA';
                break;
            case 'bill_of_exchange':
                $description = 'LETRA POR COBRAR';
                break;
            case 'bill_of_exchange_pay':
                $description = 'LETRA POR PAGAR';
                break;
            
        }

        return $description;
    }

    public function getDataPersonAttribute()
    {

        $record = $this->payment->associated_record_payment;

        switch ($this->instance_type) {

            case 'document':
            case 'bill_of_exchange':
            case 'bill_of_exchange_pay':
            case 'sale_note':
                $person['name'] = $record->customer->name;
                $person['number'] = $record->customer->number;
                break;
        }

        return (object) $person;
    }


    // public function scopeWhereFilterPaymentType($query, $params)
    // {

    //     return $query->whereHas('doc_payments', function($q) use($params){
    //                 $q->whereBetween('date_of_payment', [$params->date_start, $params->date_end])
    //                     ->whereHas('associated_record_payment', function($p){
    //                         $p->whereStateTypeAccepted()->whereTypeUser();
    //                     });

    //             }) 
    //             ->OrWhereHas('sln_payments', function($q) use($params){
    //                 $q->whereBetween('date_of_payment', [$params->date_start, $params->date_end])
    //                     ->whereHas('associated_record_payment', function($p){
    //                         $p->whereStateTypeAccepted()->whereTypeUser()
    //                             ->whereNotChanged();
    //                     });

    //             });

    // }
    public function scopeWhereFilterPaymentType($query, $params)
    {
        return $query->where(function ($query) use ($params) {
            $query->whereHas('doc_payments', function ($q) use ($params) {
                $q->whereBetween('date_of_payment', [$params->date_start, $params->date_end])
                    ->whereHas('associated_record_payment', function ($p) {
                        $p->whereStateTypeAccepted()->whereTypeUser();
                    });
            })
                ->orWhereHas('sln_payments', function ($q) use ($params) {
                    $q->whereBetween('date_of_payment', [$params->date_start, $params->date_end])
                        ->whereHas('associated_record_payment', function ($p) {
                            $p->whereStateTypeAccepted()->whereTypeUser()
                                ->whereNotChanged();
                        });
                })
                ->orWhereHas('bill_payments', function ($q) use ($params) {
                    $q->whereBetween('date_of_payment', [$params->date_start, $params->date_end])
                        ->whereHas('associated_record_payment', function ($p) {
                            // $p->whereStateTypeAccepted()->whereTypeUser();
                        });
                })
                ->orWhereHas('bill_pay_payments', function ($q) use ($params) {
                    $q->whereBetween('date_of_payment', [$params->date_start, $params->date_end])
                        ->whereHas('associated_record_payment', function ($p) {
                            // $p->whereStateTypeAccepted()->whereTypeUser();
                        });
                });
        });
    }
}

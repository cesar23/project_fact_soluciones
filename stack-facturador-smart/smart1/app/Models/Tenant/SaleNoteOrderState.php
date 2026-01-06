<?php

namespace App\Models\Tenant;


class SaleNoteOrderState extends ModelTenant
{
    public $timestamps = false;
    protected $table = 'sale_note_orders_states';

    protected $fillable = [
        'state_sale_note_orders_id',
        'sale_note_id'
    ];

    public function state_sale_note_orders(){
        return $this->belongsTo(StateSaleNoteOrder::class, 'state_sale_note_orders_id');
    }

    public function sale_note(){
        return $this->belongsTo(SaleNote::class, 'sale_note_id');
    }


}

<?php

namespace App\Models\Tenant;




class NoStockDocument extends ModelTenant
{
   
    public $timestamps = false;

    protected $fillable = [
        'sale_note_id',
        'document_id',
        'completed'

    ];

    public function sale_note()
    {
        return $this->belongsTo(SaleNote::class);
    }
    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}

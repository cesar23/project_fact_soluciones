<?php
namespace App\Models\Tenant;


class TransportFormatItem extends ModelTenant
{   
    protected $table = 'transport_format_items';
    protected $fillable = [
        'transport_format_id',
        'sale_note_id',
    ];

    public function transportFormat()
    {
        return $this->belongsTo(TransportFormat::class);
    }

    public function saleNote()
    {
        return $this->belongsTo(SaleNote::class);
    }
}   

<?php
namespace App\Models\Tenant;

class PlateNumberDocument extends ModelTenant
{
    protected $fillable = ['plate_number_id', 'document_id', 'sale_note_id', 'quotation_id', 'km'];
    protected $table = 'plate_numbers_documents';
    public function plateNumber()
    {
        return $this->belongsTo(PlateNumber::class);
    }
    public function document()
    {
        return $this->belongsTo(Document::class);
    }
    public function saleNote()
    {
        return $this->belongsTo(SaleNote::class);
    }
    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }
    public function getDocument()
    {
        return $this->document ?? $this->saleNote ?? $this->quotation;
    }
}   
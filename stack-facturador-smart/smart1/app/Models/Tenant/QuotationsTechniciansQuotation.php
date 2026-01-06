<?php

namespace App\Models\Tenant;

class QuotationsTechniciansQuotation extends ModelTenant
{
    protected $table = 'quotation_technicians_quotation';

    protected $fillable = [
        "quotation_id",
        "quotation_technician_id",
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function quotationTechnician()
    {
        return $this->belongsTo(QuotationsTechnicians::class, 'quotation_technician_id');
    }   
}

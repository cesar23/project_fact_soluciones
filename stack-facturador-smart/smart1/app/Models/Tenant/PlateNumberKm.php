<?php
namespace App\Models\Tenant;

class PlateNumberKm extends ModelTenant
{
    protected $fillable = ['description', 'plate_number_id'];

    public function plateNumber()
    {
        return $this->belongsTo(PlateNumber::class);
    }
} 
<?php
namespace App\Models\Tenant;

class PlateNumberModel extends ModelTenant
{
    protected $fillable = ['description', 'plate_number_brand_id'];

    public function brand()
    {
        return $this->belongsTo(PlateNumberBrand::class, 'plate_number_brand_id');
    }

    public function plateNumbers()
    {
        return $this->hasMany(PlateNumber::class);
    }
} 
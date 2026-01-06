<?php
namespace App\Models\Tenant;

class PlateNumberBrand extends ModelTenant
{
    protected $fillable = ['description'];

    public function models()
    {
        return $this->hasMany(PlateNumberModel::class);
    }

    public function plateNumbers()
    {
        return $this->hasMany(PlateNumber::class);
    }
} 
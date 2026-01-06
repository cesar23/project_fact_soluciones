<?php
namespace App\Models\Tenant;

class PlateNumberType extends ModelTenant
{
    protected $fillable = ['description'];

    public function plateNumbers()
    {
        return $this->hasMany(PlateNumber::class);
    }
} 
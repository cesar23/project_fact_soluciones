<?php
namespace App\Models\Tenant;

class PlateNumberColor extends ModelTenant
{
    protected $fillable = ['description'];

    public function plateNumbers()
    {
        return $this->hasMany(PlateNumber::class);
    }
} 
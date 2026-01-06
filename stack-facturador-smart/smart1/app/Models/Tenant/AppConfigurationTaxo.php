<?php

namespace App\Models\Tenant;


class AppConfigurationTaxo extends ModelTenant
{
    protected $table = "app_configuration_taxo";
    protected $fillable = [
        'menu',
        'route',
        'is_visible'
    ];
    
    protected $casts = [
        'is_visible' => 'boolean',
    ];

    public static function getConfigurationTaxo()
    {
        return self::first();
    }
    
    public  function roles()
    {
        return $this->hasMany(AppConfigurationTaxoRole::class);
    }   
    
}

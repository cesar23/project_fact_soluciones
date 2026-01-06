<?php

namespace App\Models\Tenant;


class AppConfigurationTaxoRole extends ModelTenant
{
    protected $table = "app_configuration_taxo_role";
    protected $fillable = [
        'app_configuration_taxo_id',
        'role_id',
        'is_visible'
    ];
    
    protected $casts = [
        'is_visible' => 'boolean',
    ];

    public static function getConfigurationTaxoRole()
    {
        return self::first();
    }
    
    public function appConfigurationTaxo()
    {
        return $this->belongsTo(AppConfigurationTaxo::class);
    }
    
    
}

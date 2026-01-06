<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuickAccess extends Model
{
    use UsesTenantConnection;
    protected $table='quick_access';
    protected $casts = [
        'active' => 'bool'
    ];
    protected $fillable = [
        'description',
        'link',
        'icons'
    ];

}

<?php

namespace Modules\Account\Models;

use App\Models\Tenant\ModelTenant;

class AccountAutomatic extends ModelTenant
{
    protected $table = 'account_automatic';
    public $incrementing = true;

    protected $fillable = [
        'description',
        'type',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    /**
     * Relación con los items del subdiario
     */
    public function items()
    {
        return $this->hasMany(AccountAutomaticItem::class);
    }
} 
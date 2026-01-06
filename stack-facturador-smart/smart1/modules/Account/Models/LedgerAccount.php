<?php

namespace Modules\Account\Models;

use App\Models\Tenant\ModelTenant;

class LedgerAccount extends ModelTenant
{
    protected $primaryKey = 'code';
    protected $table = 'ledger_accounts_tenant';
    protected $fillable = [
        'code',
        'name',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
<?php

namespace Modules\Account\Models;

use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Model;

class LedgerAccountHidden extends ModelTenant
{
    protected $primaryKey = 'code';
    protected $table = 'ledger_accounts_hidden';
    protected $fillable = [
        'code',
    ];
}
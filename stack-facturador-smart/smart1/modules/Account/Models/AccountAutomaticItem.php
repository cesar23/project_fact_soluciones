<?php

namespace Modules\Account\Models;

use Modules\Account\Models\LedgerAccount;
use App\Models\Tenant\ModelTenant;

class AccountAutomaticItem extends ModelTenant
{
    protected $table = 'account_automatic_items';
    public $incrementing = true;

    protected $fillable = [
        'code',
        'is_debit',
        'is_credit',
        'account_automatic_id',
        'info'
    ];

    protected $casts = [
        'is_debit' => 'boolean',
        'is_credit' => 'boolean'
    ];

    /**
     * Relación con el subdiario
     */
    public function accountAutomatic()
    {
        return $this->belongsTo(AccountAutomatic::class);
    }

    public function getDescription()
    {
        return LedgerAccount::where('code', $this->code)->first()->name;
    }
} 
<?php

namespace Modules\Account\Models;

use App\Models\Tenant\ModelTenant;

class AccountPeriod extends ModelTenant
{
    protected $table = 'account_periods';
    public $incrementing = true;

    protected $fillable = [
        'year',
        'total_debit',
        'total_credit',
        'balance'
    ];

    protected $casts = [
        'year' => 'date',
    ];  

    /**
     * Relación con los items del subdiario
     */
    public function months()
    {
        return $this->hasMany(AccountMonth::class);
    }

    public function calculateBalance()
    {
        $total_debit = 0;
        $total_credit = 0;
        foreach ($this->months as $month) {
            $total_debit += $month->total_debit;
            $total_credit += $month->total_credit;
        }
        $this->total_debit = $total_debit;
        $this->total_credit = $total_credit;
        $this->balance = $total_debit - $total_credit;
        $this->save();
    }
} 
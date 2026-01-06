<?php

namespace Modules\Account\Models;

use App\Models\Tenant\ModelTenant;

class AccountMonth extends ModelTenant
{
    protected $table = 'account_months';
    public $incrementing = true;

    protected $fillable = [
        'account_period_id',
        'month',
        'balance',
        'total_debit',
        'total_credit',
        'last_syncronitation'
    ];

    protected $casts = [
        'month' => 'date',
    ];  

    /**
     * Relación con los items del subdiario
     */
    public function items()
    {
        return $this->hasMany(AccountSubDiary::class);
    }


    public function calculateBalance()
    {
        $total_debit = 0;
        $total_credit = 0;
        foreach ($this->items as $item) {
            $total_debit += $item->items()->sum('debit_amount');
            $total_credit += $item->items()->sum('credit_amount');
        }
        $this->total_debit = $total_debit;
        $this->total_credit = $total_credit;
        $this->balance = $total_debit - $total_credit;
        $this->save();
    }

    /**
     * Relación con el período contable
     */
    public function accountPeriod()
    {
        return $this->belongsTo(AccountPeriod::class, 'account_period_id');
    }

    public static function setCorrelativeNumber($account_month_id){
        $prefix="05";

    }
} 
<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class LedgerAccountMovement extends Model
{
    protected $table = 'ledger_account_movements';
    protected $primaryKey = 'code';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'debit_description',
        'credit_description'
    ];

    /**
     * Obtener la descripción del débito formateada
     */
    public function getFormattedDebitDescription()
    {
        return nl2br($this->debit_description);
    }

    /**
     * Obtener la descripción del crédito formateada
     */
    public function getFormattedCreditDescription()
    {
        return nl2br($this->credit_description);
    }

    /**
     * Scope para buscar por código
     */
    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Scope para buscar por nombre
     */
    public function scopeByName($query, $name)
    {
        return $query->where('name', 'like', "%{$name}%");
    }

    /**
     * Scope para buscar por descripción de débito
     */
    public function scopeByDebitDescription($query, $description)
    {
        return $query->where('debit_description', 'like', "%{$description}%");
    }

    /**
     * Scope para buscar por descripción de crédito
     */
    public function scopeByCreditDescription($query, $description)
    {
        return $query->where('credit_description', 'like', "%{$description}%");
    }
} 
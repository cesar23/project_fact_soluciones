<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class LedgerAccountDescription extends Model
{
    protected $table = 'ledger_account_descriptions';
    protected $primaryKey = 'code';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'description'
    ];

    /**
     * Obtener la descripción formateada
     */
    public function getFormattedDescription()
    {
        return nl2br($this->description);
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
} 
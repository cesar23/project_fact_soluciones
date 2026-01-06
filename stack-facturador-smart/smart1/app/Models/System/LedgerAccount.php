<?php

namespace App\Models\System;
use Illuminate\Database\Eloquent\Model;

class LedgerAccount extends Model
{
    protected $primaryKey = 'code';
    protected $table = 'ledger_accounts';
    protected $fillable = [
        'code',
        'name',
    ];
}
<?php

namespace Modules\Account\Models;


use App\Models\Tenant\ModelTenant;
class AccountSubDiaryItem extends ModelTenant
{
    protected $table = 'account_sub_diary_items';

    protected $fillable = [
        'account_sub_diary_id',
        'code',
        'description',
        'general_description',
        'document_number',
        'correlative_number',
        'debit',
        'credit',
        'debit_amount',
        'credit_amount',
        'amount_adjustment'
    ];

    protected $casts = [
        'debit' => 'boolean',
        'credit' => 'boolean',
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'amount_adjustment' => 'decimal:2'
    ];

    /**
     * Relación con el subdiario
     */
    public function subDiary()
    {
        return $this->belongsTo(AccountSubDiary::class, 'account_sub_diary_id', 'id');
    }

    /**
     * Scope para filtrar por código de subdiario
     */
    public function scopeBySubDiaryId($query, $id)
    {
        return $query->where('account_sub_diary_id', $id);
    }

    /**
     * Scope para filtrar por tipo débito
     */
    public function scopeDebit($query)
    {
        return $query->where('debit', true);
    }

    /**
     * Scope para filtrar por tipo crédito
     */
    public function scopeCredit($query)
    {
        return $query->where('credit', true);
    }

    /**
     * Scope para buscar por número de documento
     */
    public function scopeByDocumentNumber($query, $number)
    {
        return $query->where('document_number', $number);
    }

    /**
     * Scope para buscar por descripción general
     */
    public function scopeByGeneralDescription($query, $description)
    {
        return $query->where('general_description', 'like', "%{$description}%");
    }

    /**
     * Obtener el total de débito
     */
    public function getTotalDebit()
    {
        return $this->debit ? $this->debit_amount : 0;
    }

    /**
     * Obtener el total de crédito
     */
    public function getTotalCredit()
    {
        return $this->credit ? $this->credit_amount : 0;
    }
} 
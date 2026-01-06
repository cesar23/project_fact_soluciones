<?php

namespace App\Models\Tenant\Catalogs;

use Hyn\Tenancy\Traits\UsesTenantConnection;

class NoteCreditType extends ModelCatalog
{
    use UsesTenantConnection;
    
    protected $table = "cat_note_credit_types";
    public $incrementing = false;

    public const PAYMENT_DATE_ADJUSTMENTS_CODE = '13';

    public function scopeWhereLegalDocument($query)
    {
        return $query->whereNotIn('id', ['NV', 'NC']);
    }

    public function scopeWhereNotLegalDocument($query)
    {
        return $query->whereIn('description', ['Devolución parcial', 'Devolución total']);
    }

}
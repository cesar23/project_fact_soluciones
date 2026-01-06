<?php

namespace Modules\Finance\Models;

use App\Models\Tenant\ModelTenant;

class IncomeItem extends ModelTenant
{

    public $timestamps = false;
    
    protected $fillable = [
        'income_id',
        'description',
        'total', 
    ];
    
    protected $casts = [
        'income_id' => 'int',
        'total' => 'float'
    ];

    public function income()
    {
        return $this->belongsTo(Income::class);
    }
 
}
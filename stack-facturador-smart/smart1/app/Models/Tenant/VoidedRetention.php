<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\Voided;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Tenant\Retention;

class VoidedRetention extends ModelTenant
{
    public $timestamps = false;

    protected $fillable = [
        'voided_id',
        'retention_id',
        'description',
    ];

    public function voided(): BelongsTo
    {
        return $this->belongsTo(Voided::class);
    }

    public function retention(): BelongsTo
    {
        return $this->belongsTo(Retention::class);
    }
}

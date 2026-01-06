<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplyProcess extends ModelTenant
{
    protected $table = 'supply_process';

    protected $fillable = [
        'record',
        'person_id',
        'supply_id',
        'document',
        'document_date',
        'receive_date',
        'assign_date',
        'year',
        'subject',
        'supply_office_id',
        'state',
        'location',
        'contact_person',
        'contact_phone',
        'observation_document',
        'observation_finish',
        'n_folios',
    ];

    protected $casts = [
        'document_date' => 'date',
        'receive_date' => 'date',
        'assign_date' => 'date',
    ];

    public $timestamps = false;

    public function supplyOffice(): BelongsTo
    {
        return $this->belongsTo(SupplyOffice::class);
    }

    

    public function supply(): BelongsTo
    {
        return $this->belongsTo(Supply::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
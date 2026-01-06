<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supply extends ModelTenant
{
    protected $fillable = [
        'code',
        'description',
        'person_id',
        'zone_id',
        'sector_id',
        'optional_address',
        'date_start',
        'date_end',
        'user_id',
        'state_supply_id',
        'supply_via_id',
        'old_code',
        'cod_route',
        'zone_type',
        'mz',
        'lte',
        'und',
        'number',
        'meter',
        'meter_code',
        'sewerage',
        'active',
        'observation'
    ];

    protected $dates = [
        'date_start',
        'date_end'
    ];

    protected $casts = [
        'date_start' => 'date',
        'date_end' => 'date',
        'meter' => 'boolean',
        'sewerage' => 'boolean',
        'active' => 'boolean'
    ];

    public function supplyVia(): BelongsTo
    {
        return $this->belongsTo(SupplyVia::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supplyState(): BelongsTo
    {
        return $this->belongsTo(SupplyState::class, 'state_supply_id');
    }

    public function suppliesPlansRegistered(): HasMany
    {
        return $this->hasMany(SupplyPlanRegistered::class);
    }

    public function getAddressFullAttribute()
    {
        $supply_via_type = $this->supplyVia->supplyTypeVia ?? null;
        $short = $supply_via_type->short ?? '';

        $address = [];
        
        if ($this->sector) {
            $address[] = $this->sector->name;
        }

        $via_parts = [];
        if ($short) {
            $via_parts[] = $short;
        }

        if ($this->supplyVia) {
            $via_parts[] = $this->supplyVia->name;
        }

        if (!empty($via_parts)) {
            $address[] = implode(' ', $via_parts);
        }

        if ($this->mz) {
            $address[] = "Mz {$this->mz}";
        }

        if ($this->lte) {
            $address[] = "Lte {$this->lte}";
        }

        return implode(' - ', array_filter($address));
    }
}
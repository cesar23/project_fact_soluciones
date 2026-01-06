<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplySolicitudeItem extends ModelTenant
{
    protected $table = 'supply_solicitude_items';

    protected $fillable = [
        'supply_concept_id',
        'supply_solicitude_id',
        'review_status',
        'pipe_diameter_water',
        'property_type_water',
        'pipe_length_water',
        'soil_type_water',
        'pipe_diameter_drainage',
        'property_type_drainage',
        'pipe_length_drainage',
        'soil_type_drainage',
        'water',
        'drainage',
        'connection_number_water',
        'connection_number_drainage',
        'connection_date',
        'inspector_operator_id',
        'installer_operator_id',
        'user_id',
        'modification_date',
        'photos',
    ];

    protected $casts = [
        'review_status' => 'integer',
        'property_type_water' => 'integer',
        'property_type_drainage' => 'integer',
        'water' => 'integer',
        'drainage' => 'integer',
        'connection_date' => 'date',
        'modification_date' => 'datetime',
        'photos' => 'array',
    ];

    public function supplySolicitude(): BelongsTo
    {
        return $this->belongsTo(SupplySolicitude::class, 'supply_solicitude_id');
    }

    public function inspectorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_operator_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function installerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'installer_operator_id');
    }
}
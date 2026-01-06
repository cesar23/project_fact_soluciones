<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplyPlanRegistered extends ModelTenant
{
    protected $table = 'supplies_plans_registered';

    protected $fillable = [
        'supplie_plan_id',
        'supply_id',
        'user_id',
        'contract_number',
        'observation',
        'active',
        'generation_day',
        'auto_generate',
        'start_generation_date',
        'end_generation_date'
    ];

    protected $casts = [
        'active' => 'boolean',
        'auto_generate' => 'boolean',
        'start_generation_date' => 'date',
        'end_generation_date' => 'date',
        'generation_day' => 'integer'
    ];

    public function supplyPlan(): BelongsTo
    {
        return $this->belongsTo(SupplyPlan::class, 'supplie_plan_id');
    }

    public function supply(): BelongsTo
    {
        return $this->belongsTo(Supply::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(SupplyPlanDocument::class);
    }

    public function getAddressFullAttribute()
    {
        return $this->supply->getAddressFullAttribute();
    }

    // Accessors
    public function getGenerationDayLabelAttribute(): string
    {
        return "Día " . $this->generation_day . " de cada mes";
    }

    // Scopes
    public function scopeActiveWithAutoGeneration($query)
    {
        return $query->where('active', true)->where('auto_generate', true);
    }

    public function scopeReadyForGeneration($query)
    {
        return $query->activeWithAutoGeneration()
            ->where(function ($q) {
                $q->whereNull('start_generation_date')
                  ->orWhere('start_generation_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_generation_date')
                  ->orWhere('end_generation_date', '>=', now());
            });
    }

    // Métodos de negocio
    public function shouldGenerateDocumentForMonth(int $year, int $month): bool
    {
        if (!$this->active || !$this->auto_generate) {
            return false;
        }

        // Verificar fechas límite
        $monthDate = now()->setYear($year)->setMonth($month)->startOfMonth();
        
        if ($this->start_generation_date && $monthDate->isBefore($this->start_generation_date)) {
            return false;
        }

        if ($this->end_generation_date && $monthDate->isAfter($this->end_generation_date)) {
            return false;
        }

        // Verificar si ya existe documento para este mes
        return !$this->documents()
            ->where('year', $year)
            ->where('month', $month)
            ->exists();
    }

    public function createDocumentForMonth(int $year, int $month): ?SupplyPlanDocument
    {
        if (!$this->shouldGenerateDocumentForMonth($year, $month)) {
            return null;
        }

        // Calcular fecha de vencimiento (día de generación del mes siguiente)
        $dueDate = now()
            ->setYear($year)
            ->setMonth($month)
            ->addMonth()
            ->setDay(min($this->generation_day, now()->daysInMonth));

        return SupplyPlanDocument::createPendingDocument(
            $this,
            $year,
            $month,
            $dueDate,
            $this->supplyPlan->total
        );
    }

    public function getNextGenerationDateAttribute(): ?\Carbon\Carbon
    {
        if (!$this->auto_generate) {
            return null;
        }

        $nextMonth = now()->addMonth();
        return $nextMonth->setDay(min($this->generation_day, $nextMonth->daysInMonth));
    }
}
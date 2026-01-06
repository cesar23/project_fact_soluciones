<?php

namespace App\Models\Tenant;

use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\Document;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SupplyPlanDocument extends ModelTenant
{
    protected $table = 'supply_plan_documents';

    protected $fillable = [
        'supply_plan_registered_id',
        'document_id',
        'sale_note_id',
        'year',
        'month',
        'generation_date',
        'due_date',
        'status',
        'amount',
        'document_series',
        'document_number',
        'observations',
        'user_id',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'original_status',
        'is_debt_payment',
    ];

    protected $casts = [
        'generation_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'year' => 'integer',
        'month' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'is_debt_payment' => 'boolean',
    ];

    // Relaciones
    public function supplyPlanRegistered(): BelongsTo
    {
        return $this->belongsTo(SupplyPlanRegistered::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function saleNote(): BelongsTo
    {
        return $this->belongsTo(SaleNote::class, 'sale_note_id');
    }
    public function sale_note(): BelongsTo
    {
        return $this->belongsTo(SaleNote::class, 'sale_note_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function debtDocuments()
    {
        return $this->hasMany(SupplyDebtDocument::class);
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'pending' => 'Pendiente',
            'generated' => 'Generado',
            'sent' => 'Enviado',
            'paid' => 'Pagado',
            'cancelled' => 'Cancelado'
        ];

        return $labels[$this->status] ?? 'Desconocido';
    }

    public function getMonthNameAttribute(): string
    {
        $months = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        return $months[$this->month] ?? 'Desconocido';
    }

    public function getFullDocumentNumberAttribute(): ?string
    {
        if ($this->document_series && $this->document_number) {
            return $this->document_series . '-' . $this->document_number;
        }
        return null;
    }

    public function getIsCancelledAttribute(): bool
    {
        return !is_null($this->cancelled_at);
    }

    /**
     * Static method to reverse debt payments for a cancelled document
     */
    public static function reverseDebtPaymentsForDocument($documentId, $documentType = 'document')
    {
        return \App\Services\DebtReversalService::reverseDebtPayments($documentId, $documentType);
    }

    public function getPeriodAttribute(): string
    {
        return $this->month_name . ' ' . $this->year;
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeGenerated($query)
    {
        return $query->where('status', 'generated');
    }

    public function scopeByPeriod($query, $year, $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    public function scopeCurrentMonth($query)
    {
        return $query->where('year', now()->year)->where('month', now()->month);
    }

    public function scopeDueThisMonth($query)
    {
        return $query->whereBetween('due_date', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ]);
    }

    // Métodos de negocio
    public function markAsGenerated(Document $document): bool
    {
        return $this->update([
            'status' => 'generated',
            'document_id' => $document->id,
            'document_series' => $document->series,
            'document_number' => $document->number,
            'amount' => $document->total,
            'generation_date' => now()
        ]);
    }

    public function markAsSent(): bool
    {
        return $this->update(['status' => 'sent']);
    }

    public function markAsPaid(): bool
    {
        return $this->update(['status' => 'paid']);
    }

    public function cancel(string $reason = null): bool
    {
        return $this->update([
            'status' => 'cancelled',
            'observations' => $reason ? $this->observations . "\nCancelado: " . $reason : $this->observations
        ]);
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && !in_array($this->status, ['paid', 'cancelled']);
    }

    public function canBeGenerated(): bool
    {
        return $this->status === 'pending';
    }

    public function canBeCancelled(): bool
    {
        return !in_array($this->status, ['paid', 'cancelled']);
    }

    // Método estático para crear registro de documento pendiente
    public static function createPendingDocument(
        SupplyPlanRegistered $planRegistered,
        int $year,
        int $month,
        ?Carbon $dueDate = null,
        ?float $amount = null
    ): self {
        return self::create([
            'supply_plan_registered_id' => $planRegistered->id,
            'year' => $year,
            'month' => $month,
            'status' => 'pending',
            'due_date' => $dueDate,
            'amount' => $amount ?? $planRegistered->supplyPlan->total,
            'user_id' => auth()->id()
        ]);
    }
}
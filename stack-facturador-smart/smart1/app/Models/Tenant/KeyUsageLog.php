<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeyUsageLog extends ModelTenant
{
    protected $fillable = [
        'seller_id',
        'admin_key_id',
        'document_id',
        'operation_type',
    ];

    public $timestamps = false;

    protected $casts = [
        'seller_id' => 'integer',
        'admin_key_id' => 'integer',
        'document_id' => 'integer',
    ];

    public const OPERATION_CANCELLATION = 'cancellation';
    public const OPERATION_CREDIT_NOTE = 'credit_note';

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function adminKey(): BelongsTo
    {
        return $this->belongsTo(AdminKey::class);
    }

    public function scopeByOperationType($query, string $operationType)
    {
        return $query->where('operation_type', $operationType);
    }

    public function scopeBySeller($query, int $sellerId)
    {
        return $query->where('seller_id', $sellerId);
    }

    public function scopeByAdminKey($query, int $adminKeyId)
    {
        return $query->where('admin_key_id', $adminKeyId);
    }

    public function scopeCancellations($query)
    {
        return $query->byOperationType(self::OPERATION_CANCELLATION);
    }

    public function scopeCreditNotes($query)
    {
        return $query->byOperationType(self::OPERATION_CREDIT_NOTE);
    }
}
<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminKey extends ModelTenant
{
    protected $fillable = [
        'admin_id',
        'key_code',
        'is_active',
        'expires_at',
        'max_uses',
        'current_uses',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'max_uses' => 'integer',
        'current_uses' => 'integer',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function usageLogs(): HasMany
    {
        return $this->hasMany(KeyUsageLog::class);
    }

    public function canUse(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses && $this->current_uses >= $this->max_uses) {
            return false;
        }

        return true;
    }

    public function incrementUsage(): void
    {
        $this->increment('current_uses');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeAvailable($query)
    {
        return $query->active()->notExpired()->where(function ($q) {
            $q->whereNull('max_uses')
              ->orWhereRaw('current_uses < max_uses');
        });
    }
}
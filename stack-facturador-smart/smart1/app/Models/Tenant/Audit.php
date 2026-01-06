<?php

namespace App\Models\Tenant;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Audit
 *
 * Sistema de auditoría para rastrear cambios en documentos
 *
 * @property int $id
 * @property string $auditable_type
 * @property int $auditable_id
 * @property string $event
 * @property string|null $field_name
 * @property string|null $old_value
 * @property string|null $new_value
 * @property string|null $related_type
 * @property int|null $related_id
 * @property int|null $user_id
 * @property string|null $description
 * @property string|null $ip_address
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read Model $auditable
 * @property-read User|null $user
 */
class Audit extends ModelTenant
{
    use UsesTenantConnection;

    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'event',
        'field_name',
        'old_value',
        'new_value',
        'related_type',
        'related_id',
        'user_id',
        'description',
        'ip_address',
    ];

    protected $casts = [
        'auditable_id' => 'integer',
        'related_id' => 'integer',
        'user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Constantes para eventos comunes
     */
    const EVENT_CREATED = 'created';
    const EVENT_UPDATED = 'updated';
    const EVENT_DELETED = 'deleted';
    const EVENT_VOIDED = 'voided';
    const EVENT_GENERATED_FROM = 'generated_from';
    const EVENT_CONVERTED_TO = 'converted_to';
    const EVENT_PAYMENT_ADDED = 'payment_added';

    /**
     * Relación polimórfica con el modelo auditado
     *
     * @return MorphTo
     */
    public function auditable()
    {
        return $this->morphTo();
    }

    /**
     * Usuario que realizó el cambio
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class)->withDefault([
            'name' => 'Sistema',
        ]);
    }

    /**
     * Obtener el modelo relacionado (para conversiones de documentos)
     *
     * @return Model|null
     */
    public function getRelatedModel()
    {
        if (!$this->related_type || !$this->related_id) {
            return null;
        }

        // Mapeo de tipos a modelos
        $modelMap = [
            'document' => Document::class,
            'sale_note' => SaleNote::class,
            'quotation' => Quotation::class,
        ];

        $modelClass = $modelMap[$this->related_type] ?? null;

        if ($modelClass) {
            return $modelClass::find($this->related_id);
        }

        return null;
    }

    /**
     * Scope para filtrar por tipo de modelo auditado
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForType($query, $type)
    {
        return $query->where('auditable_type', $type);
    }

    /**
     * Scope para filtrar por evento
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $event
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForEvent($query, $event)
    {
        return $query->where('event', $event);
    }

    /**
     * Scope para filtrar por usuario
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para filtrar cambios del sistema
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSystemChanges($query)
    {
        return $query->whereNull('user_id');
    }

    /**
     * Scope para filtrar cambios de usuarios
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUserChanges($query)
    {
        return $query->whereNotNull('user_id');
    }

    /**
     * Verificar si el cambio fue realizado por el sistema
     *
     * @return bool
     */
    public function isSystemChange()
    {
        return is_null($this->user_id);
    }

    /**
     * Obtener nombre del usuario o "Sistema"
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->user ? $this->user->name : 'Sistema';
    }

    /**
     * Obtener descripción del evento en español
     *
     * @return string
     */
    public function getEventDescription()
    {
        $descriptions = [
            self::EVENT_CREATED => 'Creado',
            self::EVENT_UPDATED => 'Actualizado',
            self::EVENT_DELETED => 'Eliminado',
            self::EVENT_VOIDED => 'Anulado',
            self::EVENT_GENERATED_FROM => 'Generado desde',
            self::EVENT_CONVERTED_TO => 'Convertido a',
            self::EVENT_PAYMENT_ADDED => 'Pago agregado',
        ];

        return $descriptions[$this->event] ?? $this->event;
    }
}

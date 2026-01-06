<?php

namespace App\Traits;

use App\Models\Tenant\Audit;
use App\Models\Tenant\Configuration;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Trait Auditable
 *
 * Agregar este trait a modelos que necesiten auditoría
 * Ejemplo: Document, SaleNote, Quotation
 */
trait Auditable
{
    /**
     * Relación polimórfica con auditorías
     *
     * @return MorphMany
     */
    public function audits()
    {
        return $this->morphMany(Audit::class, 'auditable')->orderBy('created_at', 'desc');
    }

    /**
     * Registrar un evento de auditoría
     *
     * @param string $event Tipo de evento (created, updated, deleted, voided, etc)
     * @param string|null $fieldName Campo modificado (opcional)
     * @param string|null $oldValue Valor anterior (opcional)
     * @param string|null $newValue Valor nuevo (opcional)
     * @param string|null $description Descripción personalizada (opcional)
     * @param array $options Opciones adicionales: related_type, related_id, user_id
     * @return Audit|null
     */
    public function audit(
        $event,
        $fieldName = null,
        $oldValue = null,
        $newValue = null,
        $description = null,
        array $options = []
    ) {
        // Verificar si la auditoría está habilitada
        if (!$this->isAuditEnabled()) {
            return null;
        }

        return Audit::create([
            'auditable_type' => $this->getAuditableType(),
            'auditable_id' => $this->id,
            'event' => $event,
            'field_name' => $fieldName,
            'old_value' => $oldValue !== null ? (string) $oldValue : null,
            'new_value' => $newValue !== null ? (string) $newValue : null,
            'related_type' => $options['related_type'] ?? null,
            'related_id' => $options['related_id'] ?? null,
            'user_id' => $options['user_id'] ?? $this->getCurrentUserId(),
            'description' => $description,
            'ip_address' => Request::ip(),
        ]);
    }

    /**
     * Registrar creación del modelo
     *
     * @param string|null $description
     * @return Audit|null
     */
    public function auditCreated($description = null)
    {
        return $this->audit(
            Audit::EVENT_CREATED,
            null,
            null,
            null,
            $description ?? $this->getDefaultCreatedDescription()
        );
    }

    /**
     * Registrar actualización de un campo
     *
     * @param string $fieldName
     * @param mixed $oldValue
     * @param mixed $newValue
     * @param string|null $description
     * @return Audit|null
     */
    public function auditUpdated($fieldName, $oldValue, $newValue, $description = null)
    {
        return $this->audit(
            Audit::EVENT_UPDATED,
            $fieldName,
            $oldValue,
            $newValue,
            $description ?? "Editado"
        );
    }

    /**
     * Registrar eliminación del modelo
     *
     * @param string|null $description
     * @return Audit|null
     */
    public function auditDeleted($description = null)
    {
        return $this->audit(
            Audit::EVENT_DELETED,
            null,
            null,
            null,
            $description ?? $this->getDefaultDeletedDescription()
        );
    }

    /**
     * Registrar anulación del modelo
     *
     * @param string|null $description
     * @return Audit|null
     */
    public function auditVoided($description = null)
    {
        return $this->audit(
            Audit::EVENT_VOIDED,
            'state_type_id',
            $this->getOriginal('state_type_id'),
            $this->state_type_id,
            $description ?? $this->getDefaultVoidedDescription()
        );
    }

    /**
     * Registrar que este modelo fue generado desde otro
     *
     * @param string $relatedType Tipo del modelo relacionado (ej: 'quotation', 'sale_note')
     * @param int $relatedId ID del modelo relacionado
     * @param string|null $description
     * @return Audit|null
     */
    public function auditGeneratedFrom($relatedType, $relatedId, $description = null)
    {
        return $this->audit(
            Audit::EVENT_GENERATED_FROM,
            null,
            null,
            null,
            $description ?? $this->getDefaultGeneratedFromDescription($relatedType, $relatedId),
            [
                'related_type' => $relatedType,
                'related_id' => $relatedId,
            ]
        );
    }

    /**
     * Registrar que este modelo fue convertido a otro
     *
     * @param string $relatedType Tipo del modelo relacionado (ej: 'document', 'sale_note')
     * @param int $relatedId ID del modelo relacionado
     * @param string|null $description
     * @return Audit|null
     */
    public function auditConvertedTo($relatedType, $relatedId, $description = null)
    {
        return $this->audit(
            Audit::EVENT_CONVERTED_TO,
            null,
            null,
            null,
            $description ?? $this->getDefaultConvertedToDescription($relatedType, $relatedId),
            [
                'related_type' => $relatedType,
                'related_id' => $relatedId,
            ]
        );
    }

    /**
     * Registrar múltiples cambios de campos
     *
     * @param array $changes Array asociativo ['campo' => ['old' => valor, 'new' => valor]]
     * @param string|null $description
     * @return void
     */
    public function auditMultipleChanges(array $changes, $description = null)
    {
        foreach ($changes as $fieldName => $values) {
            $this->auditUpdated(
                $fieldName,
                $values['old'] ?? null,
                $values['new'] ?? null,
                $description
            );
        }
    }

    /**
     * Registrar cuando se añade un pago al documento
     *
     * @param float $amount Monto del pago
     * @param string|null $paymentMethod Método de pago (opcional)
     * @param string|null $description Descripción personalizada (opcional)
     * @param array $options Opciones adicionales: related_type, related_id, user_id
     * @return Audit|null
     */
    public function auditPaymentAdded($amount, $paymentMethod = null, $description = null, array $options = [])
    {
        $amountFormatted = number_format($amount, 2);

        $defaultDescription = "Pago agregado por {$amountFormatted}";
        if ($paymentMethod) {
            $defaultDescription .= " - {$paymentMethod}";
        }

        return $this->audit(
            'payment_added',
            'payment',
            null,
            $amountFormatted,
            $description ?? $defaultDescription,
            $options
        );
    }

    /**
     * Obtener tipo auditable para la base de datos
     * Override este método si necesitas un nombre diferente
     *
     * @return string
     */
    protected function getAuditableType()
    {
        // Convierte "App\Models\Tenant\Document" a "document"
        $className = class_basename($this);
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
    }

    /**
     * Obtener ID del usuario actual o null si es el sistema
     *
     * @return int|null
     */
    protected function getCurrentUserId()
    {
        try {
            return Auth::check() ? Auth::id() : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Descripción por defecto para creación
     *
     * @return string
     */
    protected function getDefaultCreatedDescription()
    {
        $type = $this->getAuditableType();
        $identifier = $this->getIdentifierForAudit();
        return ucfirst($type) . " {$identifier} creado";
    }

    /**
     * Descripción por defecto para eliminación
     *
     * @return string
     */
    protected function getDefaultDeletedDescription()
    {
        $type = $this->getAuditableType();
        $identifier = $this->getIdentifierForAudit();
        return ucfirst($type) . " {$identifier} eliminado";
    }

    /**
     * Descripción por defecto para anulación
     *
     * @return string
     */
    protected function getDefaultVoidedDescription()
    {
        $type = $this->getAuditableType();
        $identifier = $this->getIdentifierForAudit();
        return ucfirst($type) . " {$identifier} anulado";
    }

    /**
     * Descripción por defecto para generación desde otro documento
     *
     * @param string $relatedType
     * @param int $relatedId
     * @return string
     */
    protected function getDefaultGeneratedFromDescription($relatedType, $relatedId)
    {
        $type = $this->getAuditableType();
        $identifier = $this->getIdentifierForAudit();
        return ucfirst($type) . " {$identifier} generado desde " . ucfirst($relatedType);
    }

    /**
     * Descripción por defecto para conversión a otro documento
     *
     * @param string $relatedType
     * @param int $relatedId
     * @return string
     */
    protected function getDefaultConvertedToDescription($relatedType, $relatedId)
    {
        $type = $this->getAuditableType();
        $identifier = $this->getIdentifierForAudit();
        return ucfirst($type) . " {$identifier} convertido a " . ucfirst($relatedType) . " #{$relatedId}";
    }

    /**
     * Obtener identificador del modelo para descripciones
     * Override este método para personalizar
     *
     * @return string
     */
    protected function getIdentifierForAudit()
    {
        // Intenta usar campos comunes para identificar el modelo
        if (isset($this->number_full)) {
            return $this->number_full;
        }

        if (isset($this->identifier)) {
            return $this->identifier;
        }

        if (isset($this->series) && isset($this->number)) {
            return "{$this->series}-{$this->number}";
        }

        return "#{$this->id}";
    }

    /**
     * Verificar si la auditoría está habilitada
     *
     * @return bool
     */
    protected function isAuditEnabled()
    {
        static $auditEnabled = null;

        // Cache en memoria para evitar múltiples queries en la misma request
        if ($auditEnabled === null) {
            try {
                $config = Configuration::getConfig();
                $auditEnabled = $config && $config->audit_sales ? true : false;
            } catch (\Exception $e) {
                $auditEnabled = false;
            }
        }

        return $auditEnabled;
    }
}

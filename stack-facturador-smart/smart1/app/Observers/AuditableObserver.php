<?php

namespace App\Observers;

use App\Models\Tenant\Audit;
use Illuminate\Database\Eloquent\Model;

/**
 * Observer para modelos con trait Auditable
 *
 * Captura automáticamente eventos de creación, actualización y eliminación
 * Para habilitar, agregar en AppServiceProvider:
 *
 * Document::observe(AuditableObserver::class);
 * SaleNote::observe(AuditableObserver::class);
 * Quotation::observe(AuditableObserver::class);
 */
class AuditableObserver
{
    /**
     * Campos que no deben ser auditados
     *
     * @var array
     */
    protected $excludedFields = [
        'updated_at',
        'created_at',
    ];

    /**
     * Handle the "created" event
     *
     * @param Model $model
     * @return void
     */
    public function created(Model $model)
    {
        if (method_exists($model, 'auditCreated')) {
            $model->auditCreated();
        }
    }

    /**
     * Handle the "updated" event
     *
     * @param Model $model
     * @return void
     */
    public function updated(Model $model)
    {
        if (!method_exists($model, 'auditUpdated')) {
            return;
        }

        // Obtener cambios del modelo
        $changes = $model->getDirty();

        foreach ($changes as $field => $newValue) {
            // Omitir campos excluidos
            if (in_array($field, $this->excludedFields)) {
                continue;
            }

            $oldValue = $model->getOriginal($field);

            // Solo auditar si realmente cambió el valor
            if ($oldValue != $newValue) {
                $model->auditUpdated(
                    $field,
                    $oldValue,
                    $newValue,
                    $this->getUpdateDescription($model, $field, $oldValue, $newValue)
                );
            }
        }
    }

    /**
     * Handle the "deleted" event
     *
     * @param Model $model
     * @return void
     */
    public function deleted(Model $model)
    {
        if (method_exists($model, 'auditDeleted')) {
            $model->auditDeleted();
        }
    }

    /**
     * Generar descripción para actualización de campo
     *
     * @param Model $model
     * @param string $field
     * @param mixed $oldValue
     * @param mixed $newValue
     * @return string
     */
    protected function getUpdateDescription(Model $model, $field, $oldValue, $newValue)
    {
        $identifier = $this->getModelIdentifier($model);
        $fieldLabel = $this->getFieldLabel($field);

        // Casos especiales
        if ($field === 'state_type_id') {
            return "{$identifier}: Estado cambiado de '{$oldValue}' a '{$newValue}'";
        }

        if ($field === 'total') {
            return "{$identifier}: Total cambiado de {$oldValue} a {$newValue}";
        }

        return "{$identifier}: {$fieldLabel} actualizado";
    }

    /**
     * Obtener identificador legible del modelo
     *
     * @param Model $model
     * @return string
     */
    protected function getModelIdentifier(Model $model)
    {
        if (isset($model->number_full)) {
            return $model->number_full;
        }

        if (isset($model->identifier)) {
            return $model->identifier;
        }

        if (isset($model->series) && isset($model->number)) {
            return "{$model->series}-{$model->number}";
        }

        $className = class_basename($model);
        return "{$className} #{$model->id}";
    }

    /**
     * Obtener etiqueta legible para el campo
     *
     * @param string $field
     * @return string
     */
    protected function getFieldLabel($field)
    {
        // Mapeo de campos comunes a etiquetas legibles
        $labels = [
            'state_type_id' => 'Estado',
            'total' => 'Total',
            'total_discount' => 'Descuento Total',
            'customer_id' => 'Cliente',
            'date_of_issue' => 'Fecha de Emisión',
            'currency_type_id' => 'Moneda',
            'payment_method_type_id' => 'Método de Pago',
            'observation' => 'Observación',
            'total_igv' => 'IGV',
            'total_taxed' => 'Base Imponible',
        ];

        return $labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }
}

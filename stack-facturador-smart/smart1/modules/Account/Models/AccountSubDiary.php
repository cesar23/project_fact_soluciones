<?php

namespace Modules\Account\Models;

use App\Models\Tenant\ModelTenant;

class AccountSubDiary extends ModelTenant
{
    protected $table = 'account_sub_diaries';
    public $incrementing = true;

    protected $fillable = [
        'code',
        'date',
        'description',
        'book_code',
        'complete',
        'account_month_id',
        'is_manual',
        'correlative_number',
        'amount_adjustment'
    ];

    protected $casts = [
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'complete' => 'boolean',
        'is_manual' => 'boolean',
        'amount_adjustment' => 'decimal:2'
    ];

    /**
     * Relación con los items del subdiario
     */
    public function items()
    {
        return $this->hasMany(AccountSubDiaryItem::class);
    }


    public function accountMonth()
    {
        return $this->belongsTo(AccountMonth::class);
    }

    /**
     * Boot del modelo para manejar eventos
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            // Solo asignar correlativo si no es manual o no tiene correlativo
            if (!$model->is_manual || !$model->correlative_number) {
                $model->correlative_number = $model->getNextCorrelative();
            }
        });
        
        static::deleted(function ($model) {
            // Recalcular correlativos del grupo después de eliminar
            $model->recalculateGroupCorrelatives();
        });
        
        static::updated(function ($model) {
            // Si cambió la fecha o código, recalcular correlativos
            if ($model->isDirty(['date', 'code', 'account_month_id'])) {
                $model->recalculateGroupCorrelatives();
            }
        });
    }

    /**
     * Obtiene el siguiente correlativo disponible para este código y mes
     */
    public function getNextCorrelative()
    {
        $query = self::where('code', $this->code);
        
        if ($this->account_month_id) {
            $query->where('account_month_id', $this->account_month_id);
        } else {
            $query->whereMonth('date', $this->date->month)
                  ->whereYear('date', $this->date->year);
        }
        
        // Obtener correlativos ocupados
        $ocupados = $query->whereNotNull('correlative_number')
                         ->pluck('correlative_number')
                         ->toArray();
        
        // Encontrar el primer número disponible
        $correlativo = 1;
        while (in_array($correlativo, $ocupados)) {
            $correlativo++;
        }
        
        return $correlativo;
    }

    /**
     * Recalcula los correlativos de todo el grupo código-mes
     */
    public function recalculateGroupCorrelatives()
    {
        $query = self::where('code', $this->code);
        
        if ($this->account_month_id) {
            $query->where('account_month_id', $this->account_month_id);
        } else {
            $query->whereMonth('date', $this->date->month)
                  ->whereYear('date', $this->date->year);
        }
        
        $records = $query->orderBy('date')->orderBy('id')->get();
        
        // Separar manuales de automáticos
        $manuales = $records->where('is_manual', true)->where('correlative_number', '!=', null);
        $automaticos = $records->where('is_manual', false)->concat(
            $records->where('is_manual', true)->where('correlative_number', null)
        );
        
        // Obtener correlativos ocupados por manuales
        $ocupados = $manuales->pluck('correlative_number')->toArray();
        
        // Reasignar correlativos a automáticos
        $correlativo_actual = 1;
        foreach ($automaticos as $record) {
            while (in_array($correlativo_actual, $ocupados)) {
                $correlativo_actual++;
            }
            
            $record->update(['correlative_number' => $correlativo_actual]);
            $ocupados[] = $correlativo_actual;
            $correlativo_actual++;
        }
    }



    /**
     * Calcula el correlativo dinámicamente basado en la posición del registro
     * ordenado por fecha contable dentro del mismo código y mes
     * @deprecated Usar correlative_number guardado en su lugar
     */
    public function calculateCorrelative()
    {
        if (!$this->account_month_id && !$this->date) {
            return 1;
        }

        $query = self::where('code', $this->code);
        
        if ($this->account_month_id) {
            // Si tiene account_month_id, filtrar por ese campo
            $query->where('account_month_id', $this->account_month_id);
        } else {
            // Si no tiene account_month_id, filtrar por mes/año de la fecha contable
            $query->whereMonth('date', $this->date->month)
                  ->whereYear('date', $this->date->year);
        }

        // Obtener todos los registros del mismo código y mes, ordenados por fecha contable y luego por ID
        $records = $query->orderBy('date')
                        ->orderBy('id')
                        ->get(['id', 'date']);

        // Encontrar la posición de este registro
        $position = 1;
        foreach ($records as $index => $record) {
            if ($record->id == $this->id) {
                $position = $index + 1;
                break;
            }
        }

        return $position;
    }

    
} 
<?php

namespace Modules\Sale\Models;

use App\Models\Tenant\{
    ModelTenant,
    Quotation
};


class TechnicalServiceQuotation extends ModelTenant
{

    protected $table = 'technical_service_quotations';
    public $timestamps = false;
    protected $fillable = [
        'technical_service_id',
        'quotation_id',
    ];


    /**
     * Datos para listado/edicion
     *
     * @return void
     */
    public function getRowResource()
    {
        return [
            'id' => $this->id,
            'quotation_id' => $this->quotation_id,
            'technical_service_id' => $this->technical_service_id,
        ];
    }


    public function technical_service()
    {
        return $this->belongsTo(TechnicalService::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }
}

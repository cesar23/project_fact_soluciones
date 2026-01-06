<?php

namespace App\Http\Resources\System;

use App\Models\System\PlanModule;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DigemidCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        return $this->collection->transform(function($row, $key) {
            return [
                'id' => $row->id,
                'cod_prod' => $row->cod_prod,
                'nom_prod' => $row->nom_prod,
                'concent' => $row->concent,
                'nom_form_farm' => $row->nom_form_farm,
                'nom_form_farm_simplif' => $row->nom_form_farm_simplif,
                'presentac' => $row->presentac,
                'fracciones' => $row->fracciones,
                'fec_vcto_reg_sanitario' => $row->fec_vcto_reg_sanitario,
                'num_regsan' => $row->num_regsan,
                'nom_titular' => $row->nom_titular,
                'situacion' => $row->situacion,
            ];
        });
    }
}
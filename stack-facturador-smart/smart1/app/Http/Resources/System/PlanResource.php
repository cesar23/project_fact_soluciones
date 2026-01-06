<?php

namespace App\Http\Resources\System;

use App\Models\System\PlanModule;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id, 
            'name' => $this->name,
            'pricing' => $this->pricing,
            'limit_documents' => $this->limit_documents,
            'limit_items' => $this->limit_items,
            'limit_users' => $this->limit_users,
            // 'plan_documents' => $this->plan_documents,
            'plan_documents' => [],
            'locked' => $this->locked,

            'establishments_limit' => $this->establishments_limit,
            'establishments_unlimited' => $this->establishments_unlimited,

            'sales_limit' => $this->sales_limit,
            'sales_unlimited' => $this->sales_unlimited,
            'include_sale_notes_sales_limit' => $this->include_sale_notes_sales_limit,
            'modules' => $this->modules->pluck('module_id'),
            'levels' => $this->levels->pluck('module_level_id'),
            'apps' => PlanModule::where('plan_id', $this->id)->whereHas('module', function ($query) {
                $query->where('sort', '>', 13);
            })->pluck('module_id'),
        ];
    }
}
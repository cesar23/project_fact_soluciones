<?php

namespace App\Http\Resources\System;

use App\Models\System\PlanModule;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PlanCollection extends ResourceCollection
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
                'name' => $row->name,
                'pricing' => $row->pricing,
                'limit_users' => $row->limit_users,
                'limit_documents' => $row->limit_documents,
                'limit_items' => $row->limit_items,
                // 'plan_documents' => $row->plan_documents, 
                'locked' => (bool) $row->locked, 
                
                'establishments_limit' => $row->establishments_limit,
                'establishments_unlimited' => $row->establishments_unlimited,

                'sales_limit' => $row->sales_limit,
                'sales_unlimited' => $row->sales_unlimited,
                'include_sale_notes_sales_limit' => $row->include_sale_notes_sales_limit,
                'modules' => $row->modules->pluck('module_id'),
                'levels' => $row->levels->pluck('module_level_id'),
                'apps' => PlanModule::where('plan_id', $row->id)->whereHas('module', function ($query) {
                    $query->where('sort', '>', 13);
                })->pluck('module_id'),
            ];
        });
    }
}
<?php

namespace Modules\LevelAccess\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SystemActivityTransactionCollection extends ResourceCollection
{

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->transform(function($row, $key) {
            return [
                'id' => $row->id,
                'user_id' => $row->user_id,
                'user_name' => $row->user_name,
                'date_of_issue' => $row->date_of_issue,
                'time_of_issue' => $row->time_of_issue,
                'document_type_id' => $row->document_type_id,
                'document_type_description' => $row->document_type_description,
                'series' => $row->series,
                'number' => $row->number,
                'number_full' => $row->number_full,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            
                
            ];
        });
    }

}

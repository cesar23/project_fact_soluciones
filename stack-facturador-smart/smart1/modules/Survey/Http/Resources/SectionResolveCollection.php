<?php

namespace Modules\Survey\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SectionResolveCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        return $this->collection->transform(function ($row, $key) use($request) {
            
        

            return [
                'id' => $row->id,
                'title' => $row->title,
                'subtitle' => $row->subtitle,
                'order' => $row->order,
                'questions' => $row->questions,
            ];
        });
    }


    

}

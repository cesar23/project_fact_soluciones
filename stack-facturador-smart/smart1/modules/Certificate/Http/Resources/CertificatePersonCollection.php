<?php

namespace Modules\Certificate\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CertificatePersonCollection extends ResourceCollection
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
                'series' => $row->series,
                'number' => $row->number,
                'certificate_name' => optional($row->certificate)->tag_1 ?? null,
                'person_name' => $row->tag_1,
                'person_number' => $row->tag_2,
                'course' => $row->tag_3,
                'academy' => $row->tag_4,
                'points_title' => $row->tag_5,
                'issue_date_place' => $row->tag_6,
                'resolution' => $row->tag_7,
                'certification_reason' => $row->tag_8,
                'resolution' => $row->tag_9,
                'items' => $row->items,
                'external_id' => $row->external_id,
                'active' => $row->active,
                'date' => $row->created_at->format('d/m/Y'),
                'url' => route('certificate.print', $row->external_id),
            ];
        });
    }
}
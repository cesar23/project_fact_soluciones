<?php

namespace Modules\Certificate\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CertificateCollection extends ResourceCollection
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
                'certificate_name' => $row->tag_1,
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
                'image_url' => ($row->water_mark_image !== null)
                ? asset('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'certificates' . DIRECTORY_SEPARATOR . $row->water_mark_image)
                : null,
            ];
        });
    }
}
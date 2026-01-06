<?php

namespace Modules\Payroll\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PayrollCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        return $this->collection->transform(function ($row, $key) {

            return [
                'id' => $row->id,
                'code' => $row->code,
                'name' => $row->name,
                'last_name' => $row->last_name,
                'age' => $row->age,
                'sex' => $row->sex,
                'job_title' => $row->job_title,
                'admission_date' => $row->admission_date,
                'cessation_date' => $row->cessation_date,
            ];
        });
    }
}

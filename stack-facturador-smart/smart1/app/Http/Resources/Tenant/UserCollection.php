<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class UserCollection
 *
 * @package App\Http\Resources\Tenant
 * @mixin  ResourceCollection
 */
class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Support\Collection
     */
    public function toArray($request = null)
    {
        return  $this->collection->transform(function($row, $key) {
            /** @var \App\Models\Tenant\User  $row */
            $type = '';
            switch ($row->type) {
                case 'admin':
                    $type =  'Administrador' ;
                    break;
                case 'seller':
                    $type =  'Vendedor' ;
                        break;
                case 'client':
                    $type =  'Cliente' ;
                    break;
                default:
                    # code...
                    break;
            }

            return [
                'auditor' => (bool)$row->auditor,
                'id' => $row->id,
                'email' => mb_strlen($row->email) > 25 ? mb_substr($row->email, 0, 25) . ".." : $row->email,
                'name' => strtoupper($row->name),
                'api_token' => $row->api_token,
                'pin' => $row->pin,
                'is_locked'=> (bool) $row->is_locked,
                'number' => $row->number,
                'personal_cell_phone' => $row->personal_cell_phone,
                'date_of_birth' =>  \Carbon\Carbon::parse($row->date_of_birth)->format('d-m-Y'),
                'position' => $row->position,
                'photo_filename' => $row->photo_filename !=null ? url('') . "/storage/uploads/users/{$row->photo_filename}" :  url('') . "/acorn/img/profile/profile-11.jpg" ,
                'document_id' => $row->document_id,
                'serie_id' => ($row->series_id == 0)?null:$row->series_id,
                'establishment_description' => optional($row->establishment)->description,
                'type' => $type,
                'area' => optional($row->area)->description,
                'category' => $row->category,
                'locked' => (bool) $row->locked,
                'person_name' => optional($row->person)->name,

            ];
        })->sortBy('id');
    }
}

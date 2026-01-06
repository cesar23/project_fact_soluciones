<?php

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\Person;
use App\Models\Tenant\TelephonePerson;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PersonLiteCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     * 
     * Note: For optimal performance, ensure the collection is loaded with:
     * - person_type relationship
     * - addresses relationship
     * Example: Person::with(['person_type', 'addresses'])->get()
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        $ids = $this->collection->pluck('id')->toArray();
        $telephones = TelephonePerson::whereIn('person_id', $ids)->get();
        return $this->collection->transform(function($row, $key) use ($telephones) {
            $person_type_descripton = '';
            if ($row->person_type !== null) {
                $person_type_descripton = $row->person_type->description;
            }
        
            return [
                'id' => $row->id,
                'number' => $row->number,
                'name' => $row->name,
                'type' => $person_type_descripton,
                'description' => $row->number . ' - ' . $row->name,
                'phone' => $row->telephone,
                'email' => $row->email,
                'telephone' => $row->telephone,
                'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $row->updated_at->format('Y-m-d H:i:s'),
                'telephones' => $telephones->where('person_id', $row->id)->pluck('telephone'),
                // Fields for Vue.js changeCustomer method
                'person_type_id' => $row->person_type_id,
                'addresses' => $row->addresses ? $row->addresses->map(function($address) {
                    return [
                        'id' => $address->id,
                        'address' => $address->address,
                        'district_id' => $address->district_id,
                        'province_id' => $address->province_id,
                        'department_id' => $address->department_id,
                    ];
                }) : [],
                'address' => $row->address,
                'seller_id' => $row->seller_id,
                'identity_document_type_id' => $row->identity_document_type_id,
                'auto_retention' => (bool) $row->auto_retention,
                'trade_name' => $row->trade_name,
            ];
        });
    }
}

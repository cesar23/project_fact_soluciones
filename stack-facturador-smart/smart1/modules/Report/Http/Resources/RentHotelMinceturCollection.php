<?php

namespace Modules\Report\Http\Resources;

use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\Catalogs\IdentityDocumentType;
use App\Models\Tenant\Person;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Hotel\Models\HotelRent;
use PHPUnit\Framework\MockObject\Builder\Identity;

class RentHotelMinceturCollection extends ResourceCollection
{


    public function toArray($request) {


        return $this->collection->transform(function(HotelRent $row, $key){
            $reg = null;
            $country = null;
            
            $customer_general = Person::find($row->customer_id);
            if($customer_general){
                $department = $customer_general->department;
                if($department){
                    $reg = $department->description;
                }
                $country_general = $customer_general->country;
                if($country_general){
                    $country = $country_general->description;
                }

            }
            $customer = (array)$row->customer;
            $customer_name = $customer["name"];
            $sex =  isset($customer["sex"]) ? ($customer["sex"] == 'M' ? 'Masculino' : 'Femenino') : null;
            $reason = $row->destiny;
            $document_type_id = $customer["identity_document_type_id"];
            $document_type = IdentityDocumentType::find($document_type_id)->description;
            $document_number = $customer["number"];
            $start_date = Carbon::parse($row->input_date)->format('d/m/Y');
            $end_date = Carbon::parse($row->output_date)->format('d/m/Y');
            $category = $row->room->category->description;
            $room = $row->room->name;
            $room_rastes = $row->room->rates->sum('price');
            return  [
                "customer_name" => $customer_name,
                "sex" => $sex,
                "reg" => $reg,
                "country" => $country,
                "reason" => $reason,
                "customer_document_type" => $document_type,
                "customer_document_number" => $document_number,                
                "start_date" => $start_date,
                "end_date" => $end_date,
                "category" => $category,
                "room" => $room,
                "room_rastes" => $room_rastes,
            ];

        });
    }
}

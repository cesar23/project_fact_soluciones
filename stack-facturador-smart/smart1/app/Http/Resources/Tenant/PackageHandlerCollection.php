<?php

    namespace App\Http\Resources\Tenant;

    use App\Models\Tenant\Configuration;
    use Illuminate\Http\Resources\Json\ResourceCollection;
    use App\Models\Tenant\Person;
use Carbon\Carbon;

    /**
     * Class SaleNoteCollection
     *
     * @package App\Http\Resources\Tenant
     */
    class PackageHandlerCollection extends ResourceCollection {
        /**
         * Transform the resource collection into an array.
         *
         * @param \Illuminate\Http\Request $request
         *
         * @return array|\Illuminate\Support\Collection
         */
        public function toArray($request) {
            return $this->collection->transform(function($row, $key) {
                $license_plate = Person::find($row->driver_id);
                if($license_plate){
                    $license_plate = $license_plate->barcode;
                }else{
                    $license_plate = '';
                }
                return [
                    'id' => $row->id,
                    'date_of_issue' => Carbon::parse($row->date_of_issue)->format('Y-m-d'),
                    'number' => $row->number,
                    'sender_name' => $row->sender->name,
                    'sender_number' => $row->sender->number,
                    'issuer_name' => $row->issuer->name,
                    'issuer_number' => $row->issuer->number,
                    'departure' => $row->departure,
                    'arrival' => $row->arrival,
                    'currency_type_id' => $row->currency_type_id,
                    'total' => $row->total,
                    'license_plate' => $license_plate,
                    'ticket' => $row->series."-".$row->number,
                ];
            });
        }

    }

<?php

namespace App\Imports;

use App\Models\Tenant\Person;
use App\Models\Tenant\PersonType;
use App\Models\Tenant\Zone;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;

class PersonsImport implements ToCollection
{
    use Importable;

    protected $data;

    public function collection(Collection $rows)
    {
        $total = count($rows);
        $registered = 0;
        unset($rows[0]);
        foreach ($rows as $row) {
            $type = request()->input('type');
            $identity_document_type_id = $row[0];
            $number = $row[1];
            $name = $row[2];
            $trade_name = $row[3];
            $country_id = ($row[4]) ?: 'PE';
            $location_id = $row[5];
            $department_id = null;
            $province_id = null;
            if ($location_id) {
                $department_id = substr($location_id, 0, 2);
                $province_id = substr($location_id, 0, 4);
            }
            $address = $row[6];
            $email = $row[7];
            $telephone = $row[8];
            $person_type = isset($row[9]) ? $row[9] : null;
            $internal_code = isset($row[10]) ? $row[10] : null;
            $zone = isset($row[11]) ? $row[11] : null;
            $zone_id = null;
            if ($zone) {
                $zone_found = Zone::where(DB::raw('lower(name)'), strtolower($zone))->first();
                if ($zone_found) {
                    $zone_id = $zone_found->id;
                } else {
                    $zone_id = Zone::create([
                        'name' => strtoupper($zone)
                    ])->id;
                }
            }
            $website = isset($row[12]) ? $row[12] : null;
            $observation = isset($row[13]) ? $row[13] : null;
            $seller_name = isset($row[14]) ? $row[14] : null;
            $barcode = isset($row[15]) ? $row[15] : null;
            $seller_id = null;
            if ($seller_name) {
                $seller_name = trim(strtoupper($seller_name));
                $seller = Person::where(DB::raw('upper(name)'), $seller_name)->first();
                if ($seller) {
                    $seller_id = $seller->id;
                }
            }
            //
            $person_type_model = null;
            $person_type_id = null;
            if (!empty($person_type)) {
                $person_type_model = PersonType::where('description', 'like', '%' . $person_type . '%')->first();
                if ($person_type_model !== null) {
                    $person_type_id = $person_type_model->id;
                }
            }
            $person = Person::where('type', $type)
                ->where('identity_document_type_id', $identity_document_type_id)
                ->where('number', $number)
                ->first();
            if (!$person) {
                Person::create([
                    'seller_id' => $seller_id,
                    'internal_code' => $internal_code,
                    'zone_id' => $zone_id,
                    'observation' => $observation,
                    'type' => $type,
                    'identity_document_type_id' => $identity_document_type_id,
                    'number' => $number,
                    'name' => $name,
                    'person_type_id' => $person_type_id,
                    'trade_name' => $trade_name,
                    'country_id' => $country_id,
                    'department_id' => $department_id,
                    'province_id' => $province_id,
                    'district_id' => $location_id,
                    'address' => $address,
                    'email' => $email,
                    'telephone' => $telephone,
                    'website' => $website,
                    'barcode' => $barcode,
                ]);
                $registered += 1;
            } else {
                $person->update([
                    'seller_id' => $seller_id ?: $person->seller_id,
                    'internal_code' => $internal_code ?: $person->internal_code,
                    'zone_id' => $zone_id ?: $person->zone_id,
                    'observation' => $observation ?: $person->observation,
                    'name' => $name ?: $person->name,
                    'person_type_id' => $person_type_id ?: $person->person_type_id,
                    'trade_name' => $trade_name ?: $person->trade_name,
                    'country_id' => $country_id ?: $person->country_id,
                    'department_id' => $department_id ?: $person->department_id,
                    'province_id' => $province_id ?: $person->province_id,
                    'district_id' => $location_id ?: $person->district_id,
                    'address' => $address ?: $person->address,
                    'email' => $email ?: $person->email,
                    'telephone' => $telephone ?: $person->telephone,
                    'website' => $website ?: $person->website,
                    'barcode' => $barcode ?: $person->barcode,
                ]);
                $registered += 1;
            }
        }
        $this->data = compact('total', 'registered');
    }

    public function getData()
    {
        return $this->data;
    }
}

<?php

namespace Modules\Certificate\Import;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;

use Modules\Certificate\Models\Certificate;
use Modules\Certificate\Models\CertificatePerson;
use Illuminate\Support\Str;

class CertificateImport implements ToCollection
{
    use Importable;

    protected $data;

    public function collection(Collection $rows)
    {
        $total = count($rows);
        $certicate_id = request('certicate_id');
        $certicate = Certificate::find($certicate_id);
        $registered = 0;
        unset($rows[0]);
        foreach ($rows as $row) {
            $person_name = ($row[0]) ?: null;
            $pérson_number = ($row[1]) ?: null;
            if ($person_name && $pérson_number) {
                $certificate_person = new CertificatePerson();
                $certificate_person->certificate_id = $certicate_id;
                $certificate_person->tag_1 = $person_name;
                $certificate_person->tag_2 = $pérson_number;
                $certificate_person->tag_3 = $certicate->tag_3;
                $certificate_person->tag_4 = $certicate->tag_4;
                $certificate_person->tag_5 = $certicate->tag_5;
                $certificate_person->tag_6 = $certicate->tag_6;
                $certificate_person->tag_7 = $certicate->tag_7;
                $certificate_person->tag_8 = $certicate->tag_8;
                $certificate_person->tag_9 = $certicate->tag_9;
                $certificate_person->external_id = Str::uuid();
                $items = $certicate->items;
                $certificate_person->items = $items;
                $certificate_person->save();
                $registered++;
            }

        
        }
        $this->data = compact('total', 'registered');
    }

    public function getData()
    {
        return $this->data;
    }
}

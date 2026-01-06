<?php

namespace App\Http\Controllers\Tenant;

use App\CoreFacturalo\Core;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Voided;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Models\Tenant\Company;
use App\Models\Tenant\Retention;
use App\Models\Tenant\VoidedRetention;

class RetentionVoidedController extends Controller
{
    public function store($id)
    {
        $retention = Retention::query()->find($id);
        $company = Company::query()->first();

        $soap_type_id = $company->soap_type_id;

        $date_of_reference = $retention->date_of_issue->format('Y-m-d');
        $date_of_issue = now()->format('Y-m-d');

        $record = Voided::query()
            ->where('soap_type_id', $soap_type_id)
            ->where('date_of_issue', $date_of_issue)
            // ->orderBy('identifier_number', 'desc')
            ->first();

        if (!$record) {
            $numeration = 1;
        } else {
            $identifier_array = explode('-', $record->identifier);
            $numeration = (int)$identifier_array[2] + 1;
        }

        $identifier = join('-', ['RR', Carbon::parse($date_of_issue)->format('Ymd'), $numeration]);
        // $identifier_number = $numeration;
        $filename = $company->number . '-' . $identifier;

        $document = [
            'user_id' => auth()->id(),
            'external_id' => Str::uuid(),
            'soap_type_id' => $soap_type_id,
            'state_type_id' => '01',
            'ubl_version' => '2.0',
            'date_of_issue' => $date_of_issue,
            'date_of_reference' => $date_of_reference,
            'identifier' => $identifier,
            // 'identifier_number' => $identifier_number,
            'filename' => $filename,
        ];


        $voided = Voided::query()->create($document);

        $company_data = [
            'name' => $company->name,
            'trade_name' => $company->name,
            'number' => $company->number,
        ];
        $document['documents'] = [
            [
                'document_type_id' => $retention->document_type_id,
                'series' => $retention->series,
                'number' => $retention->number,
                'description' => 'Anulación',
            ]
        ];

        $voided->retentions()->create([
            'retention_id' => $retention->id,
            'description' => 'Anulación',
        ]);

        $fact = new Core();
        $xmlUnsigned = $fact->createXmlUnsigned('voided2', $company_data, $document);
        $res = $fact->signXmlUnsigned($filename, $xmlUnsigned);

        if ($res['success']) {
            $pse_external_id = null;
            if ($res['send_to_pse']) {
                $pse_external_id = $res['external_id'];
                $voided->update([
                    'send_to_pse' => true,
                    'pse_external_id' => $pse_external_id
                ]);
            }
            $fact->setDataSoapType('voided_retention');
            $res_sender = $fact->senderXmlSignedSummary('voided', $filename, $res['xmlSigned'], $res['send_to_pse'], $pse_external_id);
            if ($res_sender['success']) {
                $voided->update([
                    'state_type_id' => '03',
                    'ticket' => $res_sender['ticket']
                ]);

//                VoidedRetention::query()
//                    ->where('voided_id', $voided->id)
                $retention->update([
                    'state_type_id' => '13'
                ]);
            }
            return $res_sender;
        }

        return $res;
    }

    public function storeExternalId($external_id)
    {
        $retention = Retention::query()->where("external_id", $external_id)->first();
        $company = Company::query()->first();

        $soap_type_id = $company->soap_type_id;

        $date_of_reference = $retention->date_of_issue->format('Y-m-d');
        $date_of_issue = now()->format('Y-m-d');

        $record = Voided::query()
            ->where('soap_type_id', $soap_type_id)
            ->where('date_of_issue', $date_of_issue)
            // ->orderBy('identifier_number', 'desc')
            ->first();

        if (!$record) {
            $numeration = 1;
        } else {
            $identifier_array = explode('-', $record->identifier);
            $numeration = (int)$identifier_array[2] + 1;
        }

        $identifier = join('-', ['RR', Carbon::parse($date_of_issue)->format('Ymd'), $numeration]);
        // $identifier_number = $numeration;
        $filename = $company->number . '-' . $identifier;

        $document = [
            'user_id' => auth()->id(),
            'external_id' => Str::uuid(),
            'soap_type_id' => $soap_type_id,
            'state_type_id' => '01',
            'ubl_version' => '2.0',
            'date_of_issue' => $date_of_issue,
            'date_of_reference' => $date_of_reference,
            'identifier' => $identifier,
            // 'identifier_number' => $identifier_number,
            'filename' => $filename,
        ];


        $voided = Voided::query()->create($document);

        $company_data = [
            'name' => $company->name,
            'trade_name' => $company->name,
            'number' => $company->number,
        ];
        $document['documents'] = [
            [
                'document_type_id' => $retention->document_type_id,
                'series' => $retention->series,
                'number' => $retention->number,
                'description' => 'Anulación',
            ]
        ];

        $voided->retentions()->create([
            'retention_id' => $retention->id,
            'description' => 'Anulación',
        ]);

        $fact = new Core();
        $xmlUnsigned = $fact->createXmlUnsigned('voided2', $company_data, $document);
        $res = $fact->signXmlUnsigned($filename, $xmlUnsigned);

        if ($res['success']) {
            $pse_external_id = null;
            if ($res['send_to_pse']) {
                $pse_external_id = $res['external_id'];
                $voided->update([
                    'send_to_pse' => true,
                    'pse_external_id' => $pse_external_id
                ]);
            }
            $fact->setDataSoapType('voided_retention');
            $res_sender = $fact->senderXmlSignedSummary('voided', $filename, $res['xmlSigned'], $res['send_to_pse'], $pse_external_id);
            if ($res_sender['success']) {
                $voided->update([
                    'state_type_id' => '03',
                    'ticket' => $res_sender['ticket']
                ]);

//                VoidedRetention::query()
//                    ->where('voided_id', $voided->id)
                $retention->update([
                    'state_type_id' => '13'
                ]);
            }
            return $res_sender;
        }

        return $res;
    }

    public function status($id)
    {
        $voided = Voided::query()->with('retentions')->find($id);
        $fact = new Core();
        $fact->setDataSoapType('voided_retention');
        $res = $fact->statusSummary($voided->filename, $voided->ticket, $voided->send_to_pse, $voided->pse_external_id);
        if ($res['success']) {
            $voided->update([
                'state_type_id' => '05'
            ]);
            foreach ($voided->retentions as $row) {
                Retention::query()
                    ->where('id', $row->retention_id)
                    ->update([
                        'state_type_id' => '11'
                    ]);
            }
            return [
                'success' => true,
                'message' => $res['description']
            ];
        }

        return $res;
    }

    public function statusTicket($ticket)
    {
        $voided = Voided::query()->with('retentions')->where("ticket",$ticket)->first();
        // dd($voided);
        $fact = new Core();
        $fact->setDataSoapType('voided_retention');
        $res = $fact->statusSummary($voided->filename, $voided->ticket, $voided->send_to_pse, $voided->pse_external_id);
        if ($res['success']) {
            $voided->update([
                'state_type_id' => '05'
            ]);
            foreach ($voided->retentions as $row) {
                Retention::query()
                    ->where('id', $row->retention_id)
                    ->update([
                        'state_type_id' => '11'
                    ]);
            }
            return [
                'success' => true,
                'message' => $res['description']
            ];
        }

        return $res;
    }
}

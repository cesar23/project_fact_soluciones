<?php

namespace App\Http\Controllers\Tenant\Api;

use Exception;
use App\Models\Tenant\Cash;
use App\Traits\PrinterTrait;
use Illuminate\Http\Request;
use App\Models\Tenant\Document;
use App\CoreFacturalo\Facturalo;
use App\Models\Tenant\StateType;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\DocumentCollection;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\Models\Tenant\Company;
use App\Models\Tenant\User;
use Facades\App\Http\Controllers\Tenant\DocumentController as DocumentControllerSend;

class DocumentController extends Controller
{
    use StorageDocument;
    use PrinterTrait;
    public function __construct()
    {
        $this->middleware('input.request:document,api', ['only' => ['store', 'storeServer']]);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $fact =  DB::connection('tenant')->transaction(function () use ($request) {
            $facturalo = new Facturalo();
            $result = $facturalo->save($request->all());
            $facturalo->createXmlUnsigned();
            $company = Company::active();
            if ($company->pse && $company->soap_type_id == '02' && $company->type_send_pse == 2) {
                $facturalo->sendPseNew();
            } else {
                $facturalo->signXmlUnsigned();
            }
            $facturalo->updateHash();
            $facturalo->updateQr();
            $facturalo->createPdf();
            $document_result = $result->getDocument();
            if ((!$company->pse || ($company->pse && $company->type_send_pse != 2)) && $document_result->state_type_id != '55') {
                $facturalo->senderXmlSignedBill();
            }
            // $facturalo->senderXmlSignedBill();
            $facturalo->sendEmail();

            return $facturalo;
        });
        $document = $fact->getDocument();
        $response = $fact->getResponse();

        $cash = Cash::where([['user_id',  User::getUserCashId()], ['state', true],])->first();
        // dd($cash);
        if ($cash != null) {
            $cash->cash_documents()->updateOrCreate(['id' => $cash->id, 'document_id' => $document->id]);
        }
        return [
            'success' => true,
            'data' => [
                'number' => $document->number_full,
                'filename' => $document->filename,
                'external_id' => $document->external_id,
                'printer'  => $this->printerName(auth()->user()->id),
                'print_ticket' => url('') . "/print/document/{$document->external_id}/ticket",
                'print_a4' => url('') . "/print/document/{$document->external_id}/a4",
                'print_a5' => url('') . "/print/document/{$document->external_id}/a5",
                'customer_address' => $document->customer->address,
                'customer_address_dev_id' => $document->customer->department_id,
                'customer_address_prov_id' => $document->customer->province_id,
                'customer_address_dis_id' => $document->customer->district_id,
                'state_type_id' => $document->state_type_id,
                'state_type_description' => $this->getStateTypeDescription($document->state_type_id),
                'number_to_letter' => $document->number_to_letter,
                'hash' => $document->hash,
                'qr' => $document->qr,
                'id' => $document->id,
            ],
            'links' => [
                'xml' => $document->download_external_xml,
                'pdf' => $document->download_external_pdf,
                'cdr' => isset($response['sent']) ? $document->download_external_cdr : '',
            ],
            'response' => isset($response['sent']) ? array_except($response, 'sent') : [],
        ];
    }

    public function send(Request $request)
    {
        $codigo_tipo_documento = $request->input('codigo_tipo_documento');
        $serie_documento = $request->input('serie_documento');
        $numero_documento = $request->input('numero_documento');
        if ($request->has('external_id')) {
            $external_id = $request->input('external_id');
            $document = Document::where('external_id', $external_id)->first();
            if (!$document) {
                throw new Exception("El documento con código externo {$external_id}, no se encuentra registrado.");
            }
            if ($document->group_id !== '01') {
                throw new Exception("El tipo de documento {$document->document_type_id} es inválido, no es posible enviar.");
            }
            $fact = new Facturalo();
            $fact->setDocument($document);
            $fact->loadXmlSigned();
            $fact->onlySenderXmlSignedBill();
            $response = $fact->getResponse();
            return [
                'success' => true,
                'data' => [
                    'number' => $document->number_full,
                    'filename' => $document->filename,
                    'external_id' => $document->external_id,
                    'state_type_id' => $document->state_type_id,
                    'state_type_description' => $this->getStateTypeDescription($document->state_type_id),
                ],
                'links' => [
                    'cdr' => $document->download_external_cdr,
                ],
                'response' => array_except($response, 'sent'),
            ];
        }
        if ($codigo_tipo_documento && $serie_documento && $numero_documento) {
            $document = Document::where('document_type_id', $codigo_tipo_documento)
                ->where('series', $serie_documento)
                ->where('number', $numero_documento)
                ->first();
            if (!$document) {
                return [
                    'success' => false,
                    'message' => 'El documento no se encuentra registrado.',
                ];
            }
            if (!in_array($document->state_type_id, ['01', '03'])) {
                return [
                    'success' => false,
                    'message' => 'El documento tiene estado ' . $this->getStateTypeDescription($document->state_type_id) . ' y no se puede enviar.',
                ];
            }
            $fact = new Facturalo();
            $fact->setDocument($document);
            $fact->loadXmlSigned();
            $fact->onlySenderXmlSignedBill();
            $response = $fact->getResponse();
            $message = null;
            if (isset($response["description"])) {
                $message = $response["description"];
            }
        
            return [
                'success' => in_array($document->state_type_id, ['05']),
                'number' => $document->number_full,
                'message' => $message,
                'response' => array_except($response, 'sent'),

            ];
        }
        return [
            'success' => false,
            'message' => 'El documento no se encuentra registrado.',
        ];
    }

    public function storeServer(Request $request)
    {
        $fact =  DB::connection('tenant')->transaction(function () use ($request) {
            $facturalo = new Facturalo();
            $facturalo->save($request->all());

            return $facturalo;
        });

        $document = $fact->getDocument();
        $data_json = $document->data_json;

        // $zipFly = new ZipFly();

        $this->uploadStorage($document->filename, base64_decode($data_json->file_xml_signed), 'signed');
        $this->uploadStorage($document->filename, base64_decode($data_json->file_pdf), 'pdf');

        $document->external_id = $data_json->external_id;
        $document->hash = $data_json->hash;
        $document->qr = $data_json->qr;
        $document->save();

        // Send SUNAT
        if ($document->group_id === '01') {
            if ($data_json->query) {
                DocumentControllerSend::send($document->id);
            }
        }

        return [
            'success' => true,
        ];
    }

    public function documentCheckServer($external_id)
    {
        $document = Document::where('external_id', $external_id)->first();

        if ($document->state_type_id === '05' && $document->group_id === '01') {
            $file_cdr = base64_encode($this->getStorage($document->filename, 'cdr'));
        } else {
            $file_cdr = null;
        }

        return [
            'success' => true,
            'state_type_id' => $document->state_type_id,
            'file_cdr' => $file_cdr,
        ];
    }

    private function getStateTypeDescription($id)
    {
        return StateType::find($id)->description;
    }

    public function lists($startDate = null, $endDate = null)
    {

        if ($startDate == null) {
            $record = Document::whereTypeUser()
                ->orderBy('date_of_issue', 'desc')
                ->take(50)
                ->get();
        } else {
            $record = Document::whereBetween('date_of_issue', [$startDate, $endDate])
                ->orderBy('date_of_issue', 'desc')
                ->get();
        }

        $records = new DocumentCollection($record);
        return $records;
    }

    public function getRecords($startDate, $endDate)
    {

        $records = Document::query();

        if ($startDate && $endDate) {
            $records->whereBetween('date_of_issue', [$startDate, $endDate]);
        }

        $records->whereTypeUser()->latest();

        return $records;
    }

    public function filterCPE($state)
    {


        $records = $this->getFilterRecords($state);

        return new DocumentCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function getFilterRecords($state)
    {

        $records = Document::query();

        if ($state != 0) {
            $records->whereTypeUser()->where("state_type_id", $state)->latest();
        } else {
            $records->whereTypeUser()->latest();
        }

        return $records;
    }

    public function updatestatus(Request $request)
    {
        $record = Document::whereExternal_id($request->externail_id)->first();
        $record->state_type_id = $request->state_type_id;
        $record->save();

        return [
            'success' => true,
        ];
    }
}

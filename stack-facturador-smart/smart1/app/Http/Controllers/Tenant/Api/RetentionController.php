<?php
namespace App\Http\Controllers\Tenant\Api;

use App\CoreFacturalo\Facturalo;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Retention;
use App\Models\Tenant\StateType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RetentionController extends Controller
{
    public function __construct()
    {
        $this->middleware('input.request:retention,api', ['only' => ['store']]);
    }

    public function send(Request $request)
    {
        $codigo_tipo_documento = $request->input('codigo_tipo_documento');
        $serie_documento = $request->input('serie_documento');
        $numero_documento = $request->input('numero_documento');
        
        $document = Retention::where('document_type_id', $codigo_tipo_documento)
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

        $facturalo = new Facturalo();
        $facturalo->setType('retention');
        $facturalo->setDocument($document);
        $facturalo->loadXmlSigned();
        $facturalo->onlySenderXmlSignedBill();

        $response = $facturalo->getResponse();
        if (!isset($response['description'])) {
            if (isset($response['message'])) {
                $response['description'] = $response['message'];
            }
        }
    
        return [
            'success' => in_array($document->state_type_id, ['05']),
            'message' => $response['description'],
            'data' => [
                'id' => $document->id,
                'response' => $response
            ]
        ];
    }
    private function getStateTypeDescription($id)
    {
        return StateType::find($id)->description;
    }

    public function store(Request $request)
    {
        $fact =  DB::connection('tenant')->transaction(function () use($request) {
            $facturalo = new Facturalo();
            $facturalo->save($request->all());
            $facturalo->createXmlUnsigned();
            $facturalo->signXmlUnsigned();
            $facturalo->createPdf();
            $facturalo->sendEmail();
            $facturalo->senderXmlSignedBill();

            return $facturalo;
        });

        $document = $fact->getDocument();
        $response = $fact->getResponse();

        return [
            'success' => true,
            'data' => [
                'number' => $document->number_full,
                'filename' => $document->filename,
                'external_id' => $document->external_id,
            ],
            'links' => [
                'xml' => $document->download_external_xml,
                'pdf' => $document->download_external_pdf,
                'cdr' => $document->download_external_cdr,
            ],
            'response' => array_except($response, 'sent')
        ];
    }
}
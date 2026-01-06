<?php

namespace App\Services;

use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\CoreFacturalo\Helpers\Xml\XmlHash;
use App\CoreFacturalo\Requests\Inputs\Functions;
use App\CoreFacturalo\WS\Reader\DomCdrReader;
use App\CoreFacturalo\WS\Response\StatusCdrResult;
use App\CoreFacturalo\WS\Services\BaseSunat;
use App\CoreFacturalo\WS\Zip\ZipFileDecompress;
use App\Models\Tenant\Catalogs\IdentityDocumentType;
use Illuminate\Support\Facades\Http;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Dispatch;
use App\Models\Tenant\DocumentFee;
use App\Models\Tenant\Invoice;
use App\Models\Tenant\Item;
use App\Models\Tenant\Voided;
use App\Models\Tenant\VoidedDocument;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;
use Illuminate\Support\Str;
use Modules\Dispatch\Models\Dispatcher;

class PseServiceDispatch
{
    use StorageDocument;
    const REGISTERED = '01';
    const SENT = '03';
    const ACCEPTED = '05';
    const OBSERVED = '07';
    const REJECTED = '09';
    const CANCELING = '13';
    const VOIDED = '11';

    protected $decompressor;
    protected $response;
    protected $cdrReader;
    protected $ip_server;
    protected $interface;
    protected $db_name;
    protected $token;
    protected $payload;
    protected $company;
    protected $document;
    protected $url;
    protected $url_send;
    protected $url_send_dispatch;
    protected $url_download;
    protected $url_voided;
    protected $user_name;
    protected $user_password;
    protected $unit_types;
    protected $characters;
    protected $dsct_item;
    protected $json_payload;
    protected $total_value;
    protected $total_igv;
    protected $basafe = 0;
    protected $basexo = 0;
    protected $basina = 0;
    protected $monigv = 0;
    protected $mondoc = 0;
    protected $monoca = 0;
    protected $dscglo = 0;
    protected $monotr = 0;
    protected $monisc = 0;
    protected $mondsc = 0;
    protected $mopedo = 0;
    protected $mongra = 0;
    protected $totant = 0;
    protected $is_31 = false;

    public function __construct(Dispatch $document)
    {
        $this->is_31 = $document->document_type_id == '31';
        $this->dsct_item = 0;
        $this->characters = [")", "(", "-", ".", "_", "/", "&", "\\", '"',"'"];
        $this->unit_types = [
            "BAR" => "BAR",
            "BG" => "BOL",
            "BO" => "BOT",
            "CA" => "LAT",
            "CG" => "TJT",
            "CU" => "VAS",
            "DS" => "DSP",
            "GLL" => "GAL",
            "GRM" => "GR",
            "JR" => "FCO",
            "KGM" => "KG",
            "KWT" => "KW",
            "LTR" => "LT",
            "MMT" => "MM",
            "ND" => "BRL",
            "NIU" => "UND",
            "PG" => "PLA",
            "PI" => "JAR",
            "PK" => "PQT",
            "PL" => "BAL",
            "SA" => "SCO",
            "TU" => "TUB",
            "EV" => "SOB",
            "MTR" => "MTS",
            "TNE" => "TON",
            "BX" => "CJ",
        ];
        $this->ip_server = config('configuration.pse_ip_server');
        $this->interface = config('configuration.pse_interface');
        $this->db_name = config('configuration.pse_db_name');
        $this->url = config('configuration.pse_url');
        $this->url_send = config('configuration.pse_url_send');
        $this->url_download = config('configuration.pse_url_download');
        $this->url_voided = config('configuration.pse_url_voided');
        $this->user_name = config('configuration.pse_user_name');
        $this->user_password = config('configuration.pse_user_password');
        $this->document = $document;

        try{
        if($this->document->website_id){
            $this->company = Company::where('website_id', $this->document->website_id)->first();
        }else{
            $this->company = Company::active();
        }
        }catch(Exception $e){
            $this->company = Company::active();
        }

        $this->payload = $this->getPayload();
    }
    private function getToken($type = 'send')
    {
        $company = $this->company;
        $pse_url = $company->pse_url;

        $pse_token = $company->pse_token;
        if (!$pse_url || !$pse_token) {
            return false;
        }
        if (substr($pse_url, -1) != '/') {
            $pse_url = $pse_url . '/';
        }
        $configuration = Configuration::first();
        $configuration->ticket_single_shipment = true;
        $configuration->save();
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $pse_token,
            'Ruc' => $company->number
        ])->get($pse_url . 'api/pse/token/' . $type);
        $status = $response->status();
        if ($status == 500) {
            throw new Exception("Error en el servidor de PSE");
        }
        $body = $response->json();
        $body_text = $response->body();
        if ($body['success'] == false) {
            if ($body["message"]) {
                throw new Exception($body["message"]);
            }
        }

        if ($status == 200) {
            $this->token = $body['token'];
            $this->ip_server = $body['ip_server'];
            $this->interface = $body['interface'];
            $this->db_name = $body['db_name'];
            $url = $body['url'];
            switch ($type) {
                case 'send':
                    $this->url_send = $url;
                    break;
                case 'send_dispatch':
                    $this->url_send_dispatch = $url;
                    break;
                case 'voided':
                    $this->url_voided = $url;
                    break;
                default:
                    $this->url_download = $url;
                    break;
            }
            return true;
        }
        return false;
    }
    public function payloadToJson2()
    {
        $this->formatDocument();
        $this->json_payload = json_encode($this->payload);
        return [
            'success' => true,
            'message' => 'Se generó el payload correctamente',
            'payload' => $this->json_payload

        ];
    }
    public function testSend()
    {
        $this->ip_server = "pc_facturaperuinterface";
        $this->interface = "usr_facturaperu";
        $this->db_name = "bd_ifacfacturaperu";
        $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpYXQiOjE3MDA5NDI0MDQsImV4cCI6MTcwMTAyODgwNH0.yBLcB43Umc8NvmmcBhKtmVPmtUiJnx8VTPUZHGR3wuQ";
        $url_send = "https://erp.integrens.com:4002/intelifac/ili/fac/interface_facturaperu/ws_sendbill/guirem";
        $this->formatDocument();

        $payload = $this->payload;
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->post($url_send, $payload);

        $status = $response->status();
        $body_text = $response->body();
        Log::info($body_text);
        $body = $response->json();

        $response = $this->format_response($body);
        $this->download_xml($response);
        $response['payload'] = $payload;
        return $response;
    }
    public function payloadToJson()
    {
        $this->formatDocument();
        $this->json_payload = json_encode($this->payload);

        return $this->json_payload;
    }
    private function coduni($unit_type)
    {
        if (array_key_exists($unit_type, $this->unit_types) == false) {
            return $unit_type;
        }
        return $this->unit_types[$unit_type];
    }

    private function getPayload()
    {

        return [
            "as_xmlcab" => "<NewDataSet/>",
            "as_xmldet" => "<NewDataSet/>",
            "as_xmldes" => "<NewDataSet/>",
            "as_xmlser" => "<NewDataSet/>",
            "as_flgprc" => "N"
        ];
    }

    private function formatNumber($number, $zeros = 8)

    {

        return str_pad($number, $zeros, '0', STR_PAD_LEFT);
    }
    function xml_for_download()
    {
        $xml = new SimpleXMLElement('<NewDataSet></NewDataSet>');
        $table1 = $xml->addChild('Table1');
        $table1->addChild('numruc', $this->company->number);
        $table1->addChild('altido', $this->document->document_type_id);
        $table1->addChild('sersun', $this->document->series);
        $table1->addChild('numsun',  $this->formatNumber($this->document->number));
        $xmlString = $xml->asXML();
        $xml_string = str_replace("\n", "", $xmlString);
        $startIndex = strpos($xml_string, '<NewDataSet>');
        $newDataSetXml = substr($xml_string, $startIndex);
        return $newDataSetXml;
    }
    public function download_file()
    {
        if($this->company->type_send_pse == 1){
            return [
                "success" => false,
                "message" => "No permitido"
            ];
        }
        if (!$this->token) {
            $has_token = $this->getToken('download');
            if (!$has_token) {
                return [
                    "success" => false,
                    "message" => "No se pudo obtener el token"
                ];
            }
        }
        $payload = $this->xml_for_download();
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/xml',
        ])->send(
            'POST',
            $this->url_download,
            ['body' => $payload]
        );

        $status = $response->status();
        $body = $response->json();

        if ($status == 200) {
            $data = $body['data'];
            if (!isset($data[0])) {
                return [
                    "success" => false,
                    "message" => "No se encontró el documento"
                ];
            }
            $data = $data[0];
            $xml_b64 = $data['arcxml'];
            $msjpro = array_key_exists('msjpro', $data) ? $data['msjpro'] : null;
            $estpro = array_key_exists('estpro', $data) ? $data['estpro'] : null;
            if ($msjpro != null) {
                $body['mensaje'] = $msjpro;
            } else {
                $body['mensaje'] = 'El documento aún está en proceso de envío';
            }

            if ($xml_b64 != '') {
                $xml_file = base64_decode($xml_b64);
                if ($xml_file != '') {
                    $this->uploadStorage($this->document->filename, $xml_file, 'signed');
                    $xmlSigned = $xml_file;
                    $helper = new XmlHash();
                    $hash = $helper->getHashSign($xmlSigned);
                    $this->document->update([
                        'hash' => $hash,
                    ]);
                }
            }

            $cdr_b64 = $data['arccdr'];
            if ($cdr_b64 != '') {
                $cdr_file = base64_decode($cdr_b64);
                $this->uploadStorage($this->document->filename, $cdr_file, 'cdr');
                $this->extractCdrInfo($cdr_file);
            } else {
                // Log::info('No se encontró cdr guía:'.$this->document->filename);
            }
        }
        $this->formatDocument();
        if ($estpro == "RE") {
            $this->document->update([
                'state_type_id' => self::REJECTED,
                'soap_shipping_response' => isset($this->response['sent']) ? $this->response : null
            ]);
            $body['rejected'] = true;
        }
        if ($this->document->state_type_id == self::ACCEPTED) {
            $this->sendFilesToWebService();
        }
        $body['payload'] = $this->payload;

        return $body;
    }

    function sendFilesToWebService()
    {
        $data = [
            'xml' => $this->document->download_external_xml,
            'pdf' => $this->document->download_external_pdf,
            'cdr' => $this->document->download_external_cdr,
            'date_of_issue' => $this->document->date_of_issue->format('Y-m-d'),
            'series' => $this->document->series,
            'number' => $this->document->number,
            'state_type_id' => $this->document->state_type_id,
            'document_type_id' => $this->document->document_type_id,
        ];
        //un log de data que es un array
        $pse_url = $this->company->pse_url;
        $pse_token = $this->company->pse_token;
        $send = new Client();
        if (substr($pse_url, -1) != '/') {
            $pse_url = $pse_url . '/';
        }
        $send->post(
            $pse_url . 'api/pse/download_files',
            [
                'form_params' => $data,
                'headers' => [
                    'Authorization' => 'Bearer ' . $pse_token,
                    'Ruc' => $this->company->number
                ]
            ],
        );


        return true;
    }
    function updateRegularizeShipping($code, $description)
    {
        if($description == "looks like we got no XML document"){
            return;
        }
        $this->document->update([
            'state_type_id' => self::REGISTERED,
            'regularize_shipping' => true,
            'response_regularize_shipping' => [
                'code' => $code,
                'description' => $description
            ]
        ]);
    }
    public function updateState($state_type_id)
    {

        $this->document->update([
            'state_type_id' => $state_type_id,
            'soap_shipping_response' => isset($this->response['sent']) ? $this->response : null
        ]);
    }
    public function validationCodeResponse($code, $message)
    {
        //Errors
        if (!is_numeric($code)) {

            if (in_array('invoice', ['retention', 'dispatch', 'perception', 'purchase_settlement'])) {
                throw new Exception("Code: {$code}; Description: {$message}");
            }

            $this->updateRegularizeShipping($code, $message);
            return;
        }


        if ((int)$code === 0) {
            $this->updateState(self::ACCEPTED);
            return;
        }
        if ((int)$code < 2000) {
            //Excepciones

            if (in_array('invoice', ['retention', 'dispatch', 'perception', 'purchase_settlement'])) {
                // if(in_array($this->type, ['retention', 'dispatch'])){
                throw new Exception("Code: {$code}; Description: {$message}");
            }

            $this->updateRegularizeShipping($code, $message);
            return;
        } elseif ((int)$code < 4000) {
            //Rechazo
            $this->updateState(self::REJECTED);
        } else {
            $this->updateState(self::OBSERVED);
            //Observaciones
        }
        return;
    }
    public function checkSignature($xmlString)
    {

        $pass = true;
        $xml = new \SimpleXMLElement($xmlString);
        $xml->registerXPathNamespace('ar', 'urn:oasis:names:specification:ubl:schema:xsd:ApplicationResponse-2');
        $xml->registerXPathNamespace('ext', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
        $xml->registerXPathNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
        $xml->registerXPathNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $xml->registerXPathNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');

        $signatureValue = $xml->xpath('//ds:Signature/ds:SignatureValue');

        if (!empty($signatureValue)) {
            $signatureValue = (string)$signatureValue[0];
            // Log::info($signatureValue);
            if (strpos($signatureValue, 'BetaPublicCert') !== false) {
                $pass = false;
            }
        }


        return $pass;
    }
    function extractCdrInfo($zip)
    {
        $this->decompressor = new ZipFileDecompress();
        $this->cdrReader = new DomCdrReader();
        $xml = $this->getXmlResponse($zip);
        $cdr = $this->cdrReader->getCdrResponse($xml);
        $not_has_beta_signature = $this->checkSignature($xml);
        if (!$not_has_beta_signature) {
            return;
        }
        $description = $cdr->getDescription();

        $code = $cdr->getCode();
        $this->response = [
            'sent' => true,
            'code' => $cdr->getCode(),
            'description' => $cdr->getDescription(),
            'notes' => $cdr->getNotes()
        ];
        $this->validationCodeResponse($code, $description);
    }
    function getXmlResponse($content)
    {
        $filter = function ($filename) {
            return 'xml' === strtolower($this->getFileExtension($filename));
        };
        $files = $this->decompressor->decompress($content, $filter);

        return 0 === count($files) ? '' : $files[0]['content'];
    }
    function getFileExtension($filename)
    {
        $lastDotPos = strrpos($filename, '.');
        if (!$lastDotPos) {
            return '';
        }

        return substr($filename, $lastDotPos + 1);
    }
    private function format_response($response_body)
    {


        if (array_key_exists(0, $response_body)) {
            $response_body = $response_body[0];
            $message = array_key_exists('msjpro', $response_body) ? $response_body['msjpro'] : null;
            $state = $response_body['estpro'];
            if ($message == null && $state == "ER") {
                $message = "Error desconocido";
            }
            return  [
                'sent' => $state != "ER",
                'code' => "0000",
                'description' => $message
            ];
        } else {
            //verifica si response_body tiene el key "Message"
            if (array_key_exists('Message', $response_body)) {
                $message = $response_body['Message'];;
                return  [
                    'sent' => false,
                    'code' => '0000',
                    'description' => $message
                ];
            } else {
                return  [
                    'sent' => false,
                    'code' => "0000",
                    'description' => "Error desconocido"
                ];
            }
        }
    }

    public function check_anulate()
    {
        $has_token = $this->getToken('voided');
        if (!$has_token) {
            return [
                "success" => false,
                "message" => "No se pudo obtener el token"
            ];
        }
        $payload = $this->xml_for_download();
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/xml',
        ])->send(
            'POST',
            $this->url_voided,
            ['body' => $payload]
        );
        $status = $response->status();
        $body = $response->json();
        if ($status == 200) {
            $data = $body['data'];
            // $message = $body['mensaje'];
            if (isset($data[0]) == false) {
                return [
                    'success' => false,
                    'message' => 'No se encontró el documento'
                ];
            }
            $data = $data[0];
            $cdr_b64 = $data['arccdr'];
            $xml_b64 = $data['arcxml'];
            $identifier = "";
            if ($xml_b64) {
                $xml_file = base64_decode($xml_b64);
                //read xml
                $xml = new SimpleXMLElement($xml_file);
                $cbc_id = $xml->xpath('//cbc:ID');
                $user_id = auth()->user()->id;
                $cbc_id = $cbc_id[0];
                $identifier = (string)$cbc_id;
                $file_name = $this->company->number . '-' . $identifier;
                $ticket = $data['nticke'];

                $external_id = Str::uuid()->toString();
                $date_of_issue = Carbon::now()->format('Y-m-d');
                $date_of_reference = $this->document->date_of_issue->format('Y-m-d');
                $soap_type_id = "02";
                $state_type_id = "05";
                $ubl_version = "2.0";
                try {
                    DB::connection('tenant')->beginTransaction();
                    $voided = Voided::create([
                        'external_id' => $external_id,
                        'user_id' => $user_id,
                        'soap_type_id' => $soap_type_id,
                        'state_type_id' => $state_type_id,
                        'ubl_version' => $ubl_version,
                        'date_of_issue' => $date_of_issue,
                        'date_of_reference' => $date_of_reference,
                        'identifier' => $identifier,
                        'filename' => $file_name,
                        'ticket' => $ticket,
                        'has_ticket' => true,
                        'has_cdr' => true,
                    ]);
                    $this->uploadStorage($file_name, $xml_file, 'signed');
                    if ($cdr_b64) {
                        $cdr_file = base64_decode($cdr_b64);
                        $this->uploadStorage($file_name, $cdr_file, 'cdr');
                        // $this->uploadStorage($this->document->filename, $cdr_file, 'cdr');
                        // $this->extractCdrInfo($cdr_file);
                    }
                    $voided_id = $voided->id;
                    VoidedDocument::create([
                        'voided_id' => $voided_id,
                        'document_id' => $this->document->id,
                        'description' => 'Anulado por PSE'
                    ]);
                    $this->changeAnulate();

                    DB::connection('tenant')->commit();
                } catch (Exception $e) {
                    DB::connection('tenant')->rollBack();
                    throw $e;
                }
            }

            $estanu = $data['estanu'];
            // $fecanu = $data['fecanu'];
            if ($estanu == "CO") {
                $body["sent"] = true;
                $this->document->update([
                    'state_type_id' => self::VOIDED,
                    'soap_shipping_response' => isset($this->response['sent']) ? $this->response : null
                ]);
            } else {
                $body["sent"] = false;
                $body["message"] = "El documento aún está en proceso de anulación";
            }
        }
        $body['payload'] = $this->payload;
        return $body;
    }
    function changeAnulate()
    {
        $data = [
            'series' => $this->document->series,
            'number' => $this->document->number,
            'state_type_id' => 11,
            'document_type_id' => $this->document->document_type_id,
        ];
        $company = $this->company;
        $pse_url = $company->pse_url;
        $pse_token = $company->pse_token;
        $send = new Client();
        if (substr($pse_url, -1) != '/') {
            $pse_url = $pse_url . '/';
        }
        $send->post(
            $pse_url . 'api/pse/anulate_document',
            [
                'form_params' => $data,
                'headers' => [
                    'Authorization' => 'Bearer ' . $pse_token,
                    'Ruc' => $company->number
                ]
            ],
        );


        return true;
    }
    public function anulatePse()
    {
        $has_token = $this->getToken();
        if (!$has_token) {
            return [
                "success" => false,
                "message" => "No se pudo obtener el token"
            ];
        }

        // return $this->download_file();
        // return;
        $this->formatDocument(true);

        $payload = $this->payload;
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/json',
        ])->post($this->url_send, $payload);

        $status = $response->status();
        $body = $response->json();

        $response = $this->format_response($body);
        // $this->download_xml($response);
        $response['payload'] = $payload;
        if ($response['sent']) {

            $this->document->update([
                'state_type_id' => self::CANCELING,
                'soap_shipping_response' => isset($this->response['sent']) ? $this->response : null
            ]);
        }
        return $response;
    }
    public function sendToPse()
    {
        $has_token = $this->getToken('send_dispatch');
        if (!$has_token) {
            return [
                "success" => false,
                "message" => "No se pudo obtener el token"
            ];
        }

        // return $this->download_file();
        // return;
        $this->formatDocument();
        Log::info("Enviando guia: \n".json_encode($this->payload));
        $payload = $this->payload;
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/json',
        ])->post($this->url_send_dispatch, $payload);

        $status = $response->status();
        $body_text = $response->body();
        Log::info($body_text);
        $body = $response->json();

        $response = $this->format_response($body);
        $this->download_xml($response);
        $response['payload'] = $payload;
        return $response;
    }
    function download_xml($response)
    {

        $sent = Functions::valueKeyInArray($response, 'sent', false);

        if ($sent) {
            $this->document->state_type_id = '03';
            $this->document->save();
            // $response =  $this->download_file();
        }
    }
    private function documentToModify($guiremcab)
    {
        $note = $this->document->note;
        $type = $note->note_type == 'credit';
        if ($type == 'credit') {
            $codmot = $note->note_credit_type_id;
        } else {
            $codmot = $note->note_debit_type_id;
        }
        $affected_document = $note->affected_document;
        $guiremcab->addChild("codmot", $codmot);
        $tidomd = $affected_document->document_type_id;
        $guiremcab->addChild("tidomd", $tidomd);
        $number_nudomd = $this->formatNumber($affected_document->number);
        $serie_nudomd = $affected_document->series;
        $guiremcab->addChild("nudomd", "$serie_nudomd-$number_nudomd");
        $guiremcab->addChild("fedomd", $affected_document->date_of_issue->format('Y-m-d'));
    }
    private function formatDocument($anulate = false)

    {
        $items = $this->document->items;
        if (count($items) != 0) {
            $this->payload['as_xmldet'] = '<NewDataSet>';
            foreach ($items as $idx => $document_item) {
                $xmldet = $this->create_as_xmldet($document_item, $idx);
                $this->payload['as_xmldet'] = $this->payload['as_xmldet'] . $xmldet;
            }
            $this->payload['as_xmldet'] = $this->payload['as_xmldet'] . '</NewDataSet>';
        }
        $xmlcab = $this->create_as_xmlcab($anulate);
        // $this->setFee();
        $this->payload['as_xmlcab'] = $xmlcab;
    }

    /**
     * Cabecera del xml,
     * guiremcab etiqueta donde debe ir 
     *
     * @param $data
     */
    private function setSeller($guiremcab)
    {
        $seller = $this->document->seller;

        if ($seller && $seller->number) {

            $identity_document_type_id = isDniOrRuc($seller->identity_document_type_id);
            $guiremcab->addChild('tidove', $identity_document_type_id);
            $guiremcab->addChild('nudove', $seller->number);
        } else {
            $guiremcab->addChild('tidove', 0);
            $guiremcab->addChild('nudove', '99999999');
        }
    }
    private function setHeader($element)
    {
        $element->addChild('numruc', $this->company->number);
        // $element->addChild('numruc', '20604665966');
        $element->addChild('altido', $this->document->document_type_id);
        $element->addChild('sersun', $this->document->series);
        $element->addChild('numsun', $this->formatNumber($this->document->number));
    }

    // function setFee()
    // {
    //     $payment_condition = $this->document->payment_condition;
    //     if ($payment_condition) {
    //         $name = $payment_condition->name;
    //         if ($name != "Contado") {
    //             $fees = DocumentFee::where('document_id', $this->document->id)->get();
    //             $this->payload['as_xmlpag'] = $this->create_as_xmlpag($fee);
    //         }
    //     }
    // }
    private function setPaymentCondition($guiremcab)
    {
        $payment_condition = $this->document->payment_condition;
        if ($payment_condition) {
            $name = $payment_condition->name;
            $cnvta = $name == "Contado" ? 'C' : 'R';

            $guiremcab->addChild('defopa', $name == "Contado" ? "Contado" : "Credito");
            $guiremcab->addChild('cndvta', $cnvta);
        } else {

            $guiremcab->addChild('defopa', 'c');
        }
    }
    function set_retention($xml)
    {
        if ($this->document->retention) {
            $retention = $this->document->retention;
            $prcret = $retention->percentage * 100;
            $monret = $retention->amount;
            // $basret = $retention->base;
            $dscglo = $this->getDscglo();
            $mondoc   = $this->basina + $this->basexo + $this->basafe + $this->monigv + $this->monisc - $dscglo;
            $xml->addChild('prcret', $prcret);
            $xml->addChild('monret', $monret);
            $xml->addChild('basret', $mondoc);
        } else {
            $xml->addChild('prcret', 0.00);
            $xml->addChild('monret', 0.00);
            $xml->addChild('basret', 0.00);
        }
    }

    function set_detraction($xml)
    {
        if ($this->document->detraction) {
            $detraction = $this->document->detraction;
            $xml->addChild('cobide', $detraction->detraction_type_id);
            $xml->addChild('ctadet', $detraction->bank_account);
            $xml->addChild('prcdet', $detraction->percentage);
            $xml->addChild('mondet', $detraction->amount);
        } else {
            $xml->addChild('cobide', '');
            $xml->addChild('ctadet', '');
            $xml->addChild('prcdet', 0.0);
            $xml->addChild('mondet', 0.0);
        }
    }
    function get_date_of_due()
    {
        $invoice = Invoice::where('document_id', $this->document->id)->first();
        if ($invoice) {
            return $invoice->date_of_due;
        }
        return $this->document->date_of_issue;
    }
    function add_guides($element)
    {
        if ($this->document->guides) {
            $numbers_concat = "";
            // $types_concat = "";
            $tidore = "09";
            foreach ($this->document->guides as $idx => $guide) {
                if ($idx == 0) {
                    $tidore = $guide->document_type_id;
                }
                $explode_guide = explode("-", $guide->number);
                $serie = $explode_guide[0];
                $number = $explode_guide[1];
                $number = $this->formatNumber($explode_guide[1], 8);
                $numbers_concat .= $serie . "-" . $number . ",";
                // $types_concat .= $guide->document_type_id.",";
            }
            $numbers_concat = substr($numbers_concat, 0, -1);
            // $types_concat = substr($types_concat, 0, -1);
            // $element->addChild('nudore', $serie."-".$number);
            $element->addChild('tidore', $tidore);
            $element->addChild('nudore', $numbers_concat);
        }
    }
    function setSender($xml)
    {

        $sender_data = [];
        if ($this->is_31) {
            $sender = $this->document->sender;
            $identity_document_type_id = $sender->identity_document_type_id;
            $identity_document_type_id = isDniOrRuc($identity_document_type_id);
            $sender_data['identity_document_type_id'] = $identity_document_type_id;
            $sender_data['name'] = $sender->name;
        } else {
            $sender_data['identity_document_type_id'] = '6';
            $sender_data['name'] = $this->company->name;
        }
        $xml->addChild('tidoir', $sender_data['identity_document_type_id']);
        $xml->addChild('nomcia',  $this->removeAccentMark($this->format_characters($sender_data['name'])));
    }
    function setRelatedDocument($xml)
    {
        $reference_document = $this->document->reference_document;
        if ($reference_document) {
            $serie = $reference_document->series;
            $number = $this->formatNumber($reference_document->number);
            $nudore = $serie . "-" . $number;
            $xml->addChild('tidore', $reference_document->document_type_id);
            $xml->addChild('dedore', $reference_document->document_type->description);
            $xml->addChild('nudore', $nudore);
            $xml->addChild('nudoir', $this->company->number);
        }
    }

    function setReceiverData($xml)
    {
        $identity_document_type_id = "";
        $identity_document_type_description = "";
        $number = "";
        $name = "";
        if ($this->is_31) {
            $receiver = $this->document->receiver;
            $identity_document_type_id = $receiver["identity_document_type_id"];
            $identity_document_type_id = isDniOrRuc($identity_document_type_id);
            $identity_document_type = IdentityDocumentType::find($identity_document_type_id);
            $identity_document_type_description = $identity_document_type->description;
            $number = $receiver["number"];
            $name = $receiver["name"];
        } else {
            $customer = $this->document->customer;
            $identity_document_type_id = $customer->identity_document_type_id;
            $identity_document_type_id = isDniOrRuc($identity_document_type_id);
            $identity_document_type_description = $customer->identity_document_type->description;
            $number = $customer->number;
            $name = $customer->name;
        }

        if ($identity_document_type_description != "RUC" && $identity_document_type_description != "DNI") {
            $identity_document_type_description = "OTROS";
        }

        $name = $this->format_characters($name);
        $xml->addChild('tidoid', $identity_document_type_id);
        $xml->addChild('dedoid', $identity_document_type_description);
        $xml->addChild('nudoid', $number);
        // $xml->addChild('codcli', $receiver->identity_document_type_id);
        $xml->addChild('coddom', '001');
        $xml->addChild('nomcli', $name);

        // descnl Descripción del 
        // canal - Varchar 40 NO 
        // desneg Descripción del 
        // negocio - Varchar 40 
        // numtel Número de 
        // teléfono del cliente - Varchar 20 

    }

    function setSendInfo($xml)
    {
        $transfer_reason_type_id = $this->document->transfer_reason_type_id ?? "01";
        $transfer_reason_type = $this->document->transfer_reason_type;
        $transfer_description = "Venta";
        if ($transfer_reason_type) {

            $transfer_description = $transfer_reason_type->description;
        
        }
        $id = $this->document->id;
        $unit_type_id = $this->document->unit_type_id;
        $unit_type_id_transform = $unit_type_id == "KGM" ? "KG" : "T";
        $xml->addChild('identi', $this->formatNumber($id, 8));
        $xml->addChild('codmot', $transfer_reason_type_id);
        if($transfer_reason_type_id == "04"){
            $xml->addChild('estdes', "0000");
            $xml->addChild('pleest', "0000");
        }
        $xml->addChild('desmot', $transfer_description);
        //desmot motivo de traslado merca mucho texto
        //detimo motivo de traslado datos mucho texto
        $xml->addChild('indtra', "N");
        $total_weight = $this->document->total_weight;
        $xml->addChild('pescrg', $total_weight);
        $xml->addChild('undcrg', $unit_type_id);

        $xml->addChild('totpes', $total_weight);
        $xml->addChild('undpes', $unit_type_id_transform);
        $xml->addChild('alundp', $unit_type_id);
        $packages_number = $this->document->packages_number;
        $xml->addChild('nrobul', $packages_number);
        $transport_mode_type_id = $this->document->transport_mode_type_id ?? "02";
        $xml->addChild('modtra', $transport_mode_type_id);
        $date_of_shipping = $this->document->date_of_shipping;
        $xml->addChild('fectra', $date_of_shipping->format('Y-m-d'));
        $xml->addChild('fecetg', $date_of_shipping->format('Y-m-d'));
    }
    function  setTransporterInfo($xml)
    {
        if (isset($this->document->dispatcher)) {
            $dispatcher = $this->document->dispatcher;
            $number = $dispatcher->number;
            $dispatcher_db = Dispatcher::where('number', $number)->first();

            if ($dispatcher_db) {
                $identity_document_type_id = $dispatcher_db->identity_document_type_id;
                $identity_document_type_id = isDniOrRuc($identity_document_type_id);
                $xml->addChild('tidotr', $identity_document_type_id);
                $xml->addChild('nudotr', $dispatcher_db->number);
                $xml->addChild('dedotr', $dispatcher_db->identity_document_type->description);
                // $xml->addChild('nombtr', $dispatcher_db->name);
                $name = $dispatcher_db->name;
                $name = strtoupper($name);
                $name = $this->removeAccentMark($name);
                $xml->addChild('nombtr', $name);
                $address = $dispatcher_db->address; 
                if($address){
                    $remove_accent_address = $this->removeAccentMark($address);
                    $xml->addChild('dirtrp', $remove_accent_address);
                }
            }
        } else {
            $xml->addChild('tidotr', '6');
            $xml->addChild('nudotr', $this->company->number);
            $xml->addChild('dedotr', 'RUC');
            //codigo transportista
            // $xml->addChild('codtrp', '01');
            //es posible company->name tenga tildes y eso no es permitido
            $name = $this->company->name;
            //a mayusculas
            $name = strtoupper($name);
            //quitar tildes
            $name = $this->removeAccentMark($name);
            $xml->addChild('nombtr', $name);
            //dirección
            $xml->addChild('dirtrp', '');
        }
    }
    function removeAccentMark($word)
    {
        $word = str_replace('á', 'a', $word);
        $word = str_replace('é', 'e', $word);
        $word = str_replace('í', 'i', $word);
        $word = str_replace('ó', 'o', $word);
        $word = str_replace('ú', 'u', $word);
        $word = str_replace('Á', 'A', $word);
        $word = str_replace('É', 'E', $word);
        $word = str_replace('Í', 'I', $word);
        $word = str_replace('Ó', 'O', $word);
        $word = str_replace('Ú', 'U', $word);
        $word = str_replace('&', '', $word);
        $word = $this->format_characters($word);
        return $word;
    }
    function setVehicleInfo($xml)
    {
        $transport_data = $this->document->transport_data;
        if ($transport_data) {
            $brand = $transport_data["brand"];
            $plate_number = $transport_data["plate_number"];
            $xml->addChild('plaveh', $plate_number);
            $xml->addChild('autmtr', $this->company->mtc_auth);
            $xml->addChild('marveh', $brand);
        }
    }
    function getNameAndLastName($full_name)
    {
        $exp = explode(",", $full_name);
        //if $exp has 2 elements, then it has name and last name
        if (count($exp) == 2) {
            $last_name = $exp[0];
            $name = $exp[1];
        } else {
            $last_name = $full_name;
            $name = $full_name;
        }
        return [
            'name' => $name,
            'last_name' => $last_name
        ];
    }
    function setDriverInfo($xml)
    {
        $driver = $this->document->driver;
        if ($driver != null) {
            $identity_document_type_id = $driver->identity_document_type_id;
            $identity_document_type_id = isDniOrRuc($identity_document_type_id);
            $xml->addChild('tidoco', $identity_document_type_id);
            $xml->addChild('nudocu', $driver->number);
            // $identity_document_type_id = $driver->identity_document_type_id;
            $identity_document_type = IdentityDocumentType::find($identity_document_type_id);
            $xml->addChild('dedocu', $identity_document_type->description);
            //codigo de coductor
            // $xml->addChild('codcho', $brand);
            $xml->addChild('tipcho', "Principal");
            $full_name = $this->getNameAndLastName($driver->name);
            $xml->addChild('nomcho', $full_name['name']);
            $xml->addChild('apecho', $full_name['last_name']);
            $xml->addChild('licveh', $driver->license);
        }
    }

    function setOriginAddress($xml)
    {
        if ($this->is_31) {
            $origin_address =  (object)$this->document->sender_address_data;
        } else {
            $origin_address = $this->document->origin;
        }
        if ($origin_address) {
            $xml->addChild('ubiori', $origin_address->location_id);
            $xml->addChild('dirori', $this->format_characters($origin_address->address));
        }
    }

    function setDeliveryAddress($xml)
    {
        if ($this->is_31) {
            $delivery_address =  (object)$this->document->receiver_address_data;
        } else {
            $delivery_address = $this->document->delivery;
        }
        $xml->addChild('ubides', $delivery_address->location_id);
        $xml->addChild('dirdes', $delivery_address->address);
        $xml->addChild('ubifis', $delivery_address->location_id);
        $xml->addChild('dirfis', $delivery_address->address);
    }
    function setConfigFormat($xml)
    {
        $xml->addChild('flgean', "N");
        $xml->addChild('tamfrm', "G");
        $xml->addChild('cfveho', "H");
        $xml->addChild('tigrin', "");
    }

    function setAdditionalInfoFormat($xml)
    {
        $xml->addChild('tidove', '6');
        $xml->addChild('nudove', $this->company->number);
        $xml->addChild('codven', 'T');
        $xml->addChild('nomvnd', '1');
        $xml->addChild('nomsup', '');
        $xml->addChild('codrut', '');
        $xml->addChild('desrut', '');
        $xml->addChild('pdfmod', 'N');
    }
    function setInfoERP($xml)
    {
        $xml->addChild('tipped', '');
        $xml->addChild('numped', '');
        $xml->addChild('fecped', $this->document->date_of_issue->format('Y-m-d'));
        $xml->addChild('coddsp', 'PDE');
        $xml->addChild('nrodsp', 0);
        $xml->addChild('codcia', '');
        $xml->addChild('coddiv', '');
        $xml->addChild('codofi', '');
    }
    private function create_as_xmlcab($anulate = false)
    {
        $xml = new SimpleXMLElement('<NewDataSet/>');
        $xml->addChild('guiremcab');
        $xml->guiremcab->addChild('ipserver', $this->ip_server);
        $xml->guiremcab->addChild('instance', $this->interface);
        $xml->guiremcab->addChild('dbname', $this->db_name);
        $this->setHeader($xml->guiremcab);
        $xml->guiremcab->addChild('fecemi', $this->document->date_of_issue->format('Y-m-d'));
        $xml->guiremcab->addChild('horemi', $this->document->time_of_issue);
        $xml->guiremcab->addChild('observ', "");
        $xml->guiremcab->addChild('texglo', "");
        $estreg = $anulate ? "AN" : "GE";
        $xml->guiremcab->addChild('estreg', $estreg);
        $this->setSender($xml->guiremcab);
        $this->setRelatedDocument($xml->guiremcab);
        $this->setReceiverData($xml->guiremcab);
        $this->setSendInfo($xml->guiremcab);
        $this->setTransporterInfo($xml->guiremcab);
        $this->setVehicleInfo($xml->guiremcab);
        $this->setDriverInfo($xml->guiremcab);
        $this->setOriginAddress($xml->guiremcab);
        $this->setDeliveryAddress($xml->guiremcab);
        $this->setConfigFormat($xml->guiremcab);
        $this->setAdditionalInfoFormat($xml->guiremcab);
        $this->setInfoERP($xml->guiremcab);
        // if ($this->document->document_type_id == "07" || $this->document->document_type_id == "08") {
        //     $this->documentToModify($xml->guiremcab);
        // }
        $xml_string = $xml->asXML();
        //remove the new line
        $xml_string = str_replace("\n", "", $xml_string);
        $startIndex = strpos($xml_string, '<NewDataSet>');
        $newDataSetXml = substr($xml_string, $startIndex);
        return $newDataSetXml;
    }

    private function format_characters($string, $length = null)
    {
        $characters = $this->characters;
        foreach ($characters as $character) {
            $string = str_replace($character, "", $string);
        }
        //remove doble space
        $string = preg_replace('/\s+/', ' ', $string);
        if ($length != null) {
            $string = substr($string, 0, $length);
        }
        return $string;
    }
    private function create_as_xmldet($document_item, $idx)

    {
        $item_ = Item::find($document_item->item_id);
        $item = $document_item->item;
        $canped = $document_item->quantity;

        // $xml->addChild('faceledet');
        $xml = new SimpleXMLElement('<guiremdet/>');
        $this->setHeader($xml);
        $xml->addChild('nroitm', $idx + 1);
        $xml->addChild('lineid', 1);
        $xml->addChild('altuni', $item->unit_type_id);
        $xml->addChild('coduni', $this->coduni($item->unit_type_id));
        $xml->addChild('canped', $document_item->quantity);
        $xml->addChild('codpro', $this->format_characters($item->internal_id ?? '0001',10));
        $xml->addChild('eanbar', '');
        //código sunat
        $xml->addChild('codsun', '');
        $xml->addChild('nompro', $this->format_characters($item->description, 24));
        $xml->addChild('nomabr', $this->format_characters($item->description, 24));
        // Log::info($this->format_characters($item->description, 24));
        // $xml->addChild('valref', $document_item->unit_value);
        // $codmar = optional($item_->brand)->name;
        // $xml->addChild('codmar', $codmar);

        $xml_string = $xml->asXML();
        $xml_string = str_replace("\n", "", $xml_string);

        $startIndex = strpos($xml_string, '<guiremdet>');
        $newDataSetXml = substr($xml_string, $startIndex);
        //remove from $newDataSetXml all \ characters
        return $newDataSetXml;
    }


    private function getDscglo()
    {
        $discount_total = 0;
        $discounts = $this->document->discounts ?? [];
        foreach ($discounts as $discount) {
            $discount_total += $discount->amount;
        }
        return $discount_total;
    }
    private function create_as_xmldes()
    {
    }
    private function create_as_xmladi($key, $fe)
    {
        $xml = new SimpleXMLElement('<doceleant/>');
        $this->setHeader($xml);
        $xml->addChild('nroitm', $key + 1);
        // $key_format = $this->formatNumber($key + 1, 3);
        $xml->addChild('tidoan', '01');
        $amount = $fe->amount;
        $this->totant += $amount;
        $doc = $fe->number;
        $doc_explode = explode('-', $doc);
        $series = $doc_explode[0];
        $number = $doc_explode[1];
        $number = $this->formatNumber($number, 8);
        $xml->addChild('docant', $series . '-' . $number);
        $xml->addChild('tidoem', 6);
        $xml->addChild('nudoem', $this->company->number);
        $xml->addChild('monant', $amount);

        $xml_string = $xml->asXML();
        $xml_string = str_replace("\n", "", $xml_string);
        $startIndex = strpos($xml_string, '<doceleant>');
        $newDataSetXml = substr($xml_string, $startIndex);
        return $newDataSetXml;
    }
    //solo para nc 13 o creditos
    private function create_as_xmlpag($key, $fe)
    {
        $xml = new SimpleXMLElement('<facelepag/>');

        $this->setHeader($xml);
        $xml->addChild('nroitm', $key + 1);
        $key_format = $this->formatNumber($key + 1, 3);
        $xml->addChild('codpag', "Cuota" . $key_format);
        $xml->addChild('monpag', $fe->amount);
        $xml->addChild('fecpag', $fe->date->format('Y-m-d'));
        $xml_string = $xml->asXML();
        $xml_string = str_replace("\n", "", $xml_string);
        $startIndex = strpos($xml_string, '<facelepag>');
        $newDataSetXml = substr($xml_string, $startIndex);
        return $newDataSetXml;
    }
    private function create_as_flgprc()
    {
    }
}

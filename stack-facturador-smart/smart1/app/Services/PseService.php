<?php

namespace App\Services;

use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\CoreFacturalo\Helpers\Xml\XmlHash;
use App\CoreFacturalo\Requests\Inputs\Functions;
use App\CoreFacturalo\WS\Reader\DomCdrReader;
use App\CoreFacturalo\WS\Response\StatusCdrResult;
use App\CoreFacturalo\WS\Services\BaseSunat;
use App\CoreFacturalo\WS\Zip\ZipFileDecompress;
use App\Http\Controllers\Tenant\ConfigurationController;
use App\Models\Tenant\AuditorHistory;
use Illuminate\Support\Facades\Http;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Document;
use App\Models\Tenant\DocumentFee;
use App\Models\Tenant\Invoice;
use App\Models\Tenant\Voided;
use App\Models\Tenant\VoidedDocument;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;
use Illuminate\Support\Str;

class PseService
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
    protected $is_export = false;

    public function __construct($document)
    {
        $this->dsct_item = 0;
        $this->characters = [")", "(", "-", ".", "_", "/", "&", "\\", '"', "'"];
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

        if (isset($this->document->website_id)) {
            $this->company = Company::where('website_id', $this->document->website_id)->first();
        } else {
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
        // ConfigurationController::clearCache();
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $pse_token,
            'Ruc' => $company->number
        ])->get($pse_url . 'api/pse/token/' . $type);
        $status = $response->status();
        if ($status == 500) {
            throw new Exception("Error en el servidor de PSE");
        }
        $body = $response->json();
        // $body_text = $response->body();
        if ($body['success'] == false) {
            if ($body["message"]) {
                throw new Exception(
                    "company: " . $company->number . " - " .
                        $body["message"]
                );
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
    function transformXmlJsonToJson($input_json) {
        // El input ya es un array, no necesitamos decodificarlo
        $data = $input_json;
        $result = [];
        
        // Procesar cada campo XML
        foreach ($data as $key => $xml_string) {
            // Si el valor no parece ser XML, lo pasamos directamente
            if (empty($xml_string) || !str_starts_with($xml_string, '<')) {
                $result[str_replace('as_', '', $key)] = $xml_string;
                continue;
            }

            // Decodificar entidades HTML en el XML
            $xml_string = html_entity_decode($xml_string, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        
            // Convertir el XML a objeto
            $xml = simplexml_load_string($xml_string);
            if ($xml === false) {
                $result[str_replace('as_', '', $key)] = [];
                continue;
            }
        
            // Convertir XML a array
            $json = json_encode($xml);
            $array = json_decode($json, true);
            
            // Limpiar el nombre del campo (quitar 'as_')
            $clean_key = str_replace('as_', '', $key);
            
            // Agregar al resultado
            $result[$clean_key] = $array;
        }

        return json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    public function payloadToJson()
    {
        $this->formatDocument();
        if(config('configuration.app_dev')){
            $this->json_payload = $this->transformXmlJsonToJson($this->payload);
        }else{
            $this->json_payload = json_encode($this->payload);
        }
        return [
            'success' => true,
            'message' => 'Se generó el payload correctamente',
            'payload' => $this->json_payload

        ];
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
            "as_xmladi" => "<NewDataSet/>",
            "as_xmlpag" => "<NewDataSet/>",
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
        // if(!$payload){
        //     return [
        //         "success" => false,
        //         "message" => "No se pudo obtener el payload"
        //     ];
        // }
        // if(!$this->url_download){
        //     return [
        //         "success" => false,
        //         "message" => "No se pudo obtener la url de descarga"
        //     ];
        // }
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
            if ($cdr_b64 != '' && strlen($cdr_b64) > 20) {
                $cdr_file = base64_decode($cdr_b64);
                $this->uploadStorage($this->document->filename, $cdr_file, 'cdr');
                $this->extractCdrInfo($cdr_file);
            } else {
            }
        }
        $this->formatDocument();
        if ($estpro == "RE") {
            $this->document->update([
                'state_type_id' => self::REJECTED,
                'response_regularize_shipping' => $this->getResponse($body),
                'regularize_shipping' => true,
            ]);
            $body['rejected'] = true;
        }
        if ($this->document->state_type_id == self::ACCEPTED) {
            $this->sendFilesToWebService();
        }
        $body['payload'] = $this->payload;

        return $body;
    }
    function getResponse($response)
    {
        if (isset($response['codigo']) && isset($response['mensaje'])) {

            $code = isset($response['codigo']) ? $response['codigo'] : 0;
            return [
                'sent' => false,
                'code' => $code,
                'description' => $response['mensaje']
            ];
        }


        return null;
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
        // $company = Company::active();
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
            if (strpos($message, 'fuera de plazo')) {
                $this->document->update([
                    'state_type_id' => self::REJECTED,
                    'response_regularize_shipping' => [
                        'code' => "0306",
                        'description' => $message
                    ],
                    'soap_shipping_response' => null,
                ]);
            }
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
                    if ($cdr_b64 != '' && strlen($cdr_b64) > 20) {
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
        // $company = Company::active();
        $pse_url = $this->company->pse_url;
        $pse_token = $this->company->pse_token;
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
                    'Ruc' => $this->company->number
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
            AuditorHistory::createAnulate($this->document);
            $this->document->update([
                'state_type_id' => self::CANCELING,
                'soap_shipping_response' => isset($this->response['sent']) ? $this->response : null
            ]);
        }
        return $response;
    }
    public function sendToPseMany($documents)
    {
        // $company = Company::active();

        // $has_token = $this->getToken();
        // if (!$has_token) {
        //     return [
        //         "success" => false,
        //         "message" => "No se pudo obtener el token"
        //     ];
        // }

        foreach ($documents as $document) {
            try {
                // $id = $document->id;
                // $pse = new PseService($document);

                // $response = $this->sendToPse();
                // $this->document = Document::find($document->id);
                // $this->formatDocument();
                // $payload = $this->payload;
                // Log::info("Enviando documento: " . $this->document->series . "-" . $this->document->number . " de la empresa: " . $company->number);
                // $response = Http::withHeaders([
                //     'Authorization' => 'Bearer ' . $this->token,
                //     'Content-Type' => 'application/json',
                // ])
                //     ->post($this->url_send, $payload);

                // $body_text = $response->body();
                // $body = $response->json();
                // Log::info($body_text);

                // $response = $this->format_response($body);
                // $this->download_xml($response);
                // $response['payload'] = $payload;
            } catch (Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }
    public function sendToPse()
    {
        try {
            $has_token = $this->getToken();
            if (!$has_token) {
                return [
                    "success" => false,
                    "message" => "No se pudo obtener el token"
                ];
            }

            // return $this->download_file();
            // return;
            $this->formatDocument();
            // Log::info("Enviando documento: " . $this->document->series . "-" . $this->document->number . " de la empresa: " . $this->company->number);
            $payload = $this->payload;
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ])
                ->post($this->url_send, $payload);

            $status = $response->status();
            $body_text = $response->body();
            // Log::info($body_text);
            $body = $response->json();

            $response = $this->format_response($body);
            $this->download_xml($response);
            $response['payload'] = $payload;
            if(!isset($response['description'])) {
                if(isset($response['message'])) {
                    $response['description'] = $response['message'];
                } else if(isset($response['mensaje'])) {
                    $response['description'] = $response['mensaje']; 
                } else {
                    $response['description'] = "El documento se envió";
                }
            }
            if(isset($response['sent']) && $response['sent'] === false) {
                if(isset($response['description'])) {
                    // Log::info("Linea 89 - Error al enviar documento {$this->document->series}-{$this->document->number} RUC {$this->company->number}: {$response['description']}");
                }
            }
            return $response;
        } catch (Exception $e) {
            return [
                "success" => false,
                "message" => $e->getMessage(),
                "description" => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ];
        }
    }
    function download_xml($response)
    {

        $sent = Functions::valueKeyInArray($response, 'sent', false);
        if ($sent) {
            $this->document->state_type_id = '03';
            $this->document->save();
            // $response =  $this->download_file();
        } else {
            $description = Functions::valueKeyInArray($response, 'description');
            if ($description) {
                $this->document->regularize_shipping = 1;
                $this->document->response_regularize_shipping = $response;
                $this->document->save();
            }
        }
    }
    private function documentToModify($facelecab)
    {
        $note = $this->document->note;
        $type = $note->note_type == 'credit';
        if ($type == 'credit') {
            $codmot = $note->note_credit_type_id;
        } else {
            $codmot = $note->note_debit_type_id;
        }
        // $affected_document = $note->affected_document;
        $facelecab->addChild("codmot", $codmot);
        $affected_document = $note->affected_document ?? $note->data_affected_document;
        $tidomd = $affected_document->document_type_id;
        $facelecab->addChild("tidomd", $tidomd);
        $number_nudomd = $this->formatNumber($affected_document->number);
        $serie_nudomd = $affected_document->series;
        $facelecab->addChild("nudomd", "$serie_nudomd-$number_nudomd");
        if(isset($affected_document->date_of_issue)){
            $facelecab->addChild("fecdomd", $affected_document->date_of_issue->format('Y-m-d'));
            
        }
    }
    private function formatDocument($anulate = false)

    {
        $items = $this->document->items;
        if (count($items) != 0) {
            foreach ($items as $item) {
                if ($item->affectation_igv_type_id == '40') {
                    $this->is_export = true;
                }
                $this->dsct_item += floatval($item->total_discount);
                $this->total_value += $item->unit_value * $item->quantity;
                $this->total_igv += $item->total_igv;
                // if($item->affectation_igv_type_id == '10'){
                //     $this->basafe += $item->unit_value * $item->quantity;
                // }
            }
        }

        $payment_condition = $this->document->payment_condition;
        if ($payment_condition) {
            $name = $payment_condition->name;
            if ($name != "Contado") {
                $fees = DocumentFee::where('document_id', $this->document->id)->get();
                $this->payload['as_xmlpag'] = '<NewDataSet>';
                foreach ($fees as $idx => $fee) {
                    $xmlpag = $this->create_as_xmlpag($idx, $fee);
                    $this->payload['as_xmlpag'] = $this->payload['as_xmlpag'] . $xmlpag;
                }
                $this->payload['as_xmlpag'] = $this->payload['as_xmlpag'] . '</NewDataSet>';
            }
        }

        $prepayments = $this->document->prepayments;
        if ($prepayments) {
            $this->payload['as_xmladi'] = '<NewDataSet>';
            foreach ($prepayments as $idx => $prepayment) {
                $xmlpag = $this->create_as_xmladi($idx, $prepayment);
                $this->payload['as_xmladi'] = $this->payload['as_xmladi'] . $xmlpag;
            }
            $this->payload['as_xmladi'] = $this->payload['as_xmladi'] . '</NewDataSet>';
        }
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
     * facelecab etiqueta donde debe ir 
     *
     * @param $data
     */
    private function setSeller($facelecab)
    {
        $seller = $this->document->seller;

        if ($seller && $seller->number && $seller->identity_document_type_id) {
            $identity_document_type_id = isDniOrRuc($seller->identity_document_type_id);
            $facelecab->addChild('tidove', $identity_document_type_id);
            $facelecab->addChild('nudove', $seller->number);
        } else {
            $facelecab->addChild('tidove', 0);
            $facelecab->addChild('nudove', '99999999');
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
    private function setPaymentCondition($facelecab)
    {
        $payment_condition = $this->document->payment_condition;
        if ($payment_condition) {
            $name = $payment_condition->name;
            $cnvta = $name == "Contado" ? 'C' : 'R';

            $facelecab->addChild('defopa', $name == "Contado" ? "Contado" : "Credito");
            $facelecab->addChild('cndvta', $cnvta);
        } else {

            $facelecab->addChild('defopa', 'c');
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
            $xml->addChild('basret', number_format($mondoc, 2, ".", ""));
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
                $guide_number = $guide->number;
                if (strpos($guide_number, '-') !== false) {
                    $explode_guide = explode("-", $guide_number);
                } else {
                    $explode_guide = explode(" ", $guide_number);
                }
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
    function removeAccentMark($word, $limit = null)
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
        $word = $this->format_characters($word, $limit);
        return $word;
    }
    private function create_as_xmlcab($anulate = false)
    {
        $xml = new SimpleXMLElement('<NewDataSet/>');
        $xml->addChild('facelecab');
        $codscg = '';
        $podscg = 0.0;
        $xml->facelecab->addChild('ipserver', $this->ip_server);
        $xml->facelecab->addChild('instance', $this->interface);
        $xml->facelecab->addChild('dbname', $this->db_name);
        $this->setHeader($xml->facelecab);
        $xml->facelecab->addChild('fecemi', $this->document->date_of_issue->format('Y-m-d'));
        $xml->facelecab->addChild('horemi', $this->document->date_of_issue->format('H:i:s'));
        // $xml->facelecab->addChild('horemi', $this->document->date_of_issue->format('09:09:09'));
        $xml->facelecab->addChild('tidosu', "01");
        $tidoid = $this->document->customer->identity_document_type->id;
        if ($this->is_export) {
            $tidoid = '0';
        }
        $tidoid = isDniOrRuc($tidoid);
        $xml->facelecab->addChild('tidoid', $tidoid);
        $xml->facelecab->addChild('numidn', $this->document->customer->number);
        $xml->facelecab->addChild('nomcli', $this->format_characters($this->document->customer->name, 50));
        $this->add_guides($xml->facelecab);
        $xml->facelecab->addChild('codmnd', $this->document->currency_type_id == 'PEN' ? 'S/' : '$');
        ///moneda
        $altmnd = $this->document->currency_type_id == 'PEN' ? 'SOLES' : 'PEN';
        $xml->facelecab->addChild('altmnd', $altmnd);
        //ni idea
        //montos
        $dscglo = $this->getDscglo();
        if ($dscglo != 0) {
            $discounts = $this->document->discounts;
            $toRest = 0;
            if ($discounts) {
                foreach ($discounts as $discount) {
                    $podscg = $discount->factor;
                    $codscg = $discount->discount_type_id;
                    if ($discount->discount_type_id == "02") {
                        $toRest += $discount->amount;
                    }
                }
            }

            $newbasafe = $this->basafe - $toRest;
            if ($newbasafe < 0) {
                $newbasafe = 0;
            }
            if ($newbasafe != $this->basafe) {
                $this->monigv = $newbasafe * 0.18;
            }
            $xml->facelecab->addChild('basafe', $newbasafe);
        } else {

            $xml->facelecab->addChild('basafe', $this->basafe);
        }
        $xml->facelecab->addChild('codscg', $codscg);
        $xml->facelecab->addChild('podscg', $podscg);
        $xml->facelecab->addChild('basina', $this->basina);
        // $xml->facelecab->addChild('basexo', number_format($this->basexo, 2, '.', ''));
        $xml->facelecab->addChild('basexo', $this->basexo);
        //CAMBIAR ! gratuitas
        $xml->facelecab->addChild('monigv', $this->monigv);
        $xml->facelecab->addChild('monisc', $this->document->total_isc);

        $xml->facelecab->addChild('dscglo', $dscglo);
        $xml->facelecab->addChild('monotr', $this->monotr);
        $mondoc   = $this->basina + $this->basexo + $this->basafe + $this->monigv + $this->monisc - $dscglo;
        // if(isCompany("20604665966")){
            // $mondoc = $this->roundUpDecimals($mondoc, 2);
        // }

        $xml->facelecab->addChild('monoca', $this->monoca);
        $xml->facelecab->addChild('mongra', $this->mongra);
        //dscto que afectan  la  base imponible, se va a "total_discount" del document, no hace falta sumar lo de los items
        $xml->facelecab->addChild('mondsc', number_format($this->dsct_item, 2, '.', ''));
        // $xml->facelecab->addChild('mondoc', number_format($mondoc, 2, '.', ''));
        $xml->facelecab->addChild('mondoc', number_format($mondoc, 2, '.', ''));
        $xml->facelecab->addChild('prcper', 0.0);
        $xml->facelecab->addChild('corepe', '');
        $xml->facelecab->addChild('basper', 0.0);
        $xml->facelecab->addChild('monper', 0.0);
        $xml->facelecab->addChild('totdoc', 0.0);
        //nota de crédito - débito
        // $xml->facelecab->addChild('tidomd', '');
        // $xml->facelecab->addChild('nudomd', '');





        //pedido
        $xml->facelecab->addChild('tipped', '');
        $xml->facelecab->addChild('fecped', $this->document->date_of_issue->format('Y-m-d'));
        // $xml->facelecab->addChild('cndvta', '');
        $xml->facelecab->addChild('codcli', '');
        $xml->facelecab->addChild('dedoid', '');
        $xml->facelecab->addChild('ubifis', $this->document->customer->district_id);
        $xml->facelecab->addChild('dirfis', $this->format_characters($this->document->customer->address));
        $xml->facelecab->addChild('desmnd', 'SOLES');
        $xml->facelecab->addChild('dester', 'CONTADO');
        $xml->facelecab->addChild('coddoc', '');
        $xml->facelecab->addChild('numdoc', '');
        $xml->facelecab->addChild('numped', '');
        $this->setSeller($xml->facelecab);

        $xml->facelecab->addChild('codven', '');
        $xml->facelecab->addChild('tipcam', $this->document->exchange_rate_sale);

        $xml->facelecab->addChild('mopedo', 0.0);
        // $xml->facelecab->addChild('todope', $this->document->total);
        $xml->facelecab->addChild('todope', number_format($mondoc, 2, '.', ''));
        // $xml->facelecab->addChild('todope', number_format($mondoc, 2, '.', ''));
        // $xml->facelecab->addChild('todope', number_format($mondoc, 2, '.', ''));
        $estreg = $anulate ? "AN" : "CO";
        $xml->facelecab->addChild('estreg', $estreg);

        //
        $xml->facelecab->addChild('tomona', 0.0);
        //ACÁ si no es de item, solo a nivel documento
        // $xml->facelecab->addChild('dscglo', $this->document->total_discount);

        $xml->facelecab->addChild('ordcom', $this->document->purchase_order);
        $xml->facelecab->addChild('flgean', '');
        $xml->facelecab->addChild('flagfe', '');
        $xml->facelecab->addChild('observ', $this->document->additional_data);
        $xml->facelecab->addChild('texglo', '');
        //detracción
        $this->set_detraction($xml->facelecab);

        $xml->facelecab->addChild('tamfrm', '');
        $xml->facelecab->addChild('cfveho', '');
        $xml->facelecab->addChild('coddsp', '');
        $xml->facelecab->addChild('nrodsp', '');
        $xml->facelecab->addChild('totant', $this->totant);
        $xml->facelecab->addChild('coddom', '');
        $xml->facelecab->addChild('numtel', '');
        $xml->facelecab->addChild('fecvct', $this->get_date_of_due()->format('Y-m-d'));
        $xml->facelecab->addChild('fecdsp', '');
        $xml->facelecab->addChild('dirofi', '');
        $xml->facelecab->addChild('monicb', '');
        $xml->facelecab->addChild('sldacl', '');
        $xml->facelecab->addChild('codter', '');
        $xml->facelecab->addChild('codcia', '');
        $xml->facelecab->addChild('coddiv', '');
        $xml->facelecab->addChild('codofi', '');
        $xml->facelecab->addChild('codrut', '');



        // $xml->facelecab->addChild('codcli', $this->document->customer->id);


        //VENDEDOR
        // $xml->facelecab->addChild('codcli',$this->document);
        // $xml->facelecab->addChild('ubifis',$this->document);
        // $xml->facelecab->addChild('dirfis',$this->document);

        // $xml->facelecab->addChild('dester', $this->document);
        // $xml->facelecab->addChild('ordcom', $this->document);
        $this->setPaymentCondition($xml->facelecab);

        // $xml->facelecab->addChild('texglo', $this->document);
        $this->set_retention($xml->facelecab);

        if ($this->document->document_type_id == "07" || $this->document->document_type_id == "08") {
            $this->documentToModify($xml->facelecab);
        }
        $xml_string = $xml->asXML();
        //remove the new line
        $xml_string = str_replace("\n", "", $xml_string);
        $startIndex = strpos($xml_string, '<NewDataSet>');
        $newDataSetXml = substr($xml_string, $startIndex);
        return $newDataSetXml;
    }
    function roundUpDecimals($number, $decimals = 2) {
        // Multiplicamos por 10^decimales para trabajar con enteros
        $multiplier = pow(10, $decimals);
        $number_shifted = $number * $multiplier;
        
        // Redondeamos hacia arriba si hay decimales adicionales
        $rounded = ceil($number_shifted);
        
        // Volvemos a la escala original
        $result = $rounded / $multiplier;
        
        // Formateamos para asegurar el número exacto de decimales
        return number_format($result, $decimals, '.', '');
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
        $item = $document_item->item;
        $canped = $document_item->quantity;
        $preuni = $document_item->unit_value - ($document_item->total_discount / $document_item->quantity);

        $totuni = $document_item->unit_value * $document_item->quantity - $document_item->total_discount;
        if ($document_item->total_isc > 0) {
            // $preuni = $preuni + $document_item->total_isc;
            // $totuni = $totuni + $document_item->total_isc;

        }

        $prcigv = 18.00;
        $affectation_igv_type_id = $document_item->affectation_igv_type_id;
        if ($affectation_igv_type_id < 20) {
            $prcigv = $document_item->percentage_igv;
            $prelis = $preuni * (1 + $document_item->percentage_isc / 100) * (1 + $document_item->percentage_igv / 100);
            // if(isCompany("20604665966")){
            // $prelis = $this->roundUpDecimals($prelis, 2);
            // $prelis = $this->roundUpDecimals($prelis, 2);
            // }
        } else {
            $prcigv = 0.0;
            $prelis = $preuni;
        }
        $valref = 0;
        switch ($affectation_igv_type_id) {
            case '10':
                $this->basafe += $totuni + $document_item->total_isc;
                break;
            case '20':
                $this->basexo += $totuni;
                break;
            case '15':
                $valref = $document_item->total_value / $document_item->quantity;
                $this->mongra += $document_item->total_value;
                break;
            case '30':
                $this->basina += $totuni;
                break;
            case '40':
                $this->basexo += $totuni;
                break;
        }
        $monigv = 0;
        if ($affectation_igv_type_id == '10') {
            $monigv = $totuni * (1 + $document_item->percentage_isc / 100) * ($document_item->percentage_igv / 100);
        }
        $montot = $prelis * $canped;
        // if(isCompany("20604665966")){
        // $montot = $this->roundUpDecimals($montot, 2);
        // }
        $this->monigv += $monigv;
        // $xml->addChild('faceledet');
        $xml = new SimpleXMLElement('<faceledet/>');
        $this->setHeader($xml);
        $xml->addChild('altuni', $item->unit_type_id);
        $xml->addChild('coduni', $this->coduni($item->unit_type_id));
        $xml->addChild('canped', $document_item->quantity);
        $xml->addChild('codpro', $item->internal_id ?? '0001');
        $xml->addChild('eanbar', '');
        $xml->addChild('nompro', $this->removeAccentMark($item->description, 24));
        $xml->addChild('nomabr', $this->removeAccentMark($item->description, 24));
        $xml->addChild('valbas', $document_item->unit_value);
        $xml->addChild('prcigv', $prcigv);
        $xml->addChild('mondsc', number_format(($document_item->total_discount), 2, '.', ''));

        $xml->addChild('preuni', $preuni);
        $xml->addChild('monigv', $monigv);
        $xml->addChild('codafe', $document_item->affectation_igv_type_id);
        $xml->addChild('desafe', '');
        $xml->addChild('monisc', $document_item->total_isc);
        $xml->addChild('tipisc', $document_item->system_isc_type_id ?? '');
        $xml->addChild('prelis', $prelis);

        $xml->addChild('totuni', $totuni);
        $xml->addChild('nroitm', $idx + 1);
        $xml->addChild('valref', $valref);
        $xml->addChild('monper', 0.0);
        $xml->addChild('montot', number_format($montot, 2, '.', ''));
        $xml->addChild('desdet', '');
        $xml->addChild('monicb', '');
        // $xml->addChild('frmvta', '');
        // $xml->addChild('cendis', '');
        // $xml->addChild('codalm', '');
        $xml->addChild('frmvta', '');
        $xml->addChild('cendis', '');
        $xml->addChild('codalm', '');
        $xml_string = $xml->asXML();
        $xml_string = str_replace("\n", "", $xml_string);

        $startIndex = strpos($xml_string, '<faceledet>');
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

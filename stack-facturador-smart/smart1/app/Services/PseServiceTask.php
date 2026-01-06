<?php

namespace App\Services;

use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\CoreFacturalo\Helpers\Xml\XmlHash;
use App\CoreFacturalo\Requests\Inputs\Functions;
use App\CoreFacturalo\WS\Reader\DomCdrReader;
use App\CoreFacturalo\WS\Zip\ZipFileDecompress;
use Illuminate\Support\Facades\Http;
use App\Models\Tenant\Company;
use App\Models\Tenant\Document;
use App\Models\Tenant\Voided;
use App\Models\Tenant\VoidedDocument;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Hyn\Tenancy\Models\Website;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;
use Illuminate\Support\Str;
use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;
use Hyn\Tenancy\Facades\TenancyFacade;
use Hyn\Tenancy\Models\Hostname;

class PseServiceTask
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
    protected $state_type_id;
    protected $documents;

    public function __construct($documents, $state_type_id, $company = null)
    {
        $this->documents = $documents;
    
        $this->state_type_id = $state_type_id;
        if($company) {
            $this->company = $company;
        } else {
            $this->company = Company::active();
        }
        $this->payload = $this->format_to_check();
        switch ($this->state_type_id) {
            case '03':
                $this->download_file();
                break;
            case '13':
                $this->check_anulate();
                break;
            default:
                # code...
                break;
        }
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
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $pse_token,
            'Ruc' => $company->number
        ])->get($pse_url . 'api/pse/token/' . $type);
        $status = $response->status();
        if ($status == 500) {
            throw new Exception("Error en el servidor de PSE");
        }
        $body = $response->json();
        if ($body['success'] == false) {
            if ($body["message"]) {
                throw new Exception($body["message"].' '.$this->company->name);
            }
        }
        // $response = Http::withBasicAuth($this->user_name, $this->user_password)->post($this->url);
        // $status = $response->status();
        // $body = $response->json();
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

    function format_to_check()
    {
        $xml = new SimpleXMLElement('<NewDataSet></NewDataSet>');
        foreach ($this->documents as $document) {
            $table1 = $xml->addChild('Table1');
            $table1->addChild('numruc', $this->company->number);
            $table1->addChild('altido', $document->document_type_id);
            $table1->addChild('sersun', $document->series);
            $table1->addChild('numsun',  $this->formatNumber($document->number));
        }
        $xmlString = $xml->asXML();
        $xml_string = str_replace("\n", "", $xmlString);
        $startIndex = strpos($xml_string, '<NewDataSet>');
        $newDataSetXml = substr($xml_string, $startIndex);
        return $newDataSetXml;
    }

    private function formatNumber($number, $zeros = 8)

    {

        return str_pad($number, $zeros, '0', STR_PAD_LEFT);
    }

    function removeZeros($str)
    {
        $resultado = ltrim($str, '0');
        return $resultado === '' ? '0' : $resultado;
    }
    function get_document($data)
    {
        $number = $this->removeZeros($data['numsun']);
        $series = $data['sersun'];
        $document = Document::where('number', $number)
            ->where('series', $series)
            ->where('soap_type_id', '02')
            ->first();
        return $document;
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
        // $payload = $this->xml_for_download();
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/xml',
        ])->send(
            'POST',
            $this->url_download,
            ['body' => $this->payload]
        );

        $status = $response->status();
        $body = $response->json();
        if ($status == 200) {
            $datas = $body['data'];
            if (!isset($datas) || count($datas) == 0) {
                return;
            }

            foreach ($datas as $data) {
                $document = $this->get_document($data);
                $this->response = [];
                $this->document = $document;
                if ($document) {
                    $xml_b64 = $data['arcxml'];
                    $msjpro = array_key_exists('msjpro', $data) ? $data['msjpro'] : null;
                    $estpro = array_key_exists('estpro', $data) ? $data['estpro'] : null;
                    if ($msjpro != null) {
                        if ($xml_b64 != '') {
                            $xml_file = base64_decode($xml_b64);
                            if ($xml_file != '') {
                                $this->uploadStorage($document->filename, $xml_file, 'signed');
                                $xmlSigned = $xml_file;
                                $helper = new XmlHash();
                                $hash = $helper->getHashSign($xmlSigned);
                                $document->update([
                                    'hash' => $hash,
                                ]);
                            }
                        }

                        $cdr_b64 = $data['arccdr'];
                        if ($cdr_b64 != '' && strlen($cdr_b64) > 20){
                            $cdr_file = base64_decode($cdr_b64);
                            $this->uploadStorage($document->filename, $cdr_file, 'cdr');
                            $this->extractCdrInfo($cdr_file);
                        } else {
                            // Log::info('No se encontró cdr:'.$document->filename);
                        }
                        if ($estpro == "RE") {
                            $document->update([
                                'state_type_id' => self::REJECTED,
                                'soap_shipping_response' => isset($this->response['sent']) ? $this->response : null
                            ]);
                            $body['rejected'] = true;
                        }
                        if ($document->state_type_id == self::ACCEPTED) {
                            $this->sendFilesToWebService();
                        }
                        $out_of_range = $this->out_of_range($msjpro);
                        if ($out_of_range) {
                            $document->update([
                                'state_type_id' => self::REJECTED,
                                'response_regularize_shipping' => [
                                    'code' => "0306",
                                    'description' => $msjpro
                                ],
                                'soap_shipping_response' => isset($this->response['sent']) ? $this->response : null
                            ]);
                        }
                    }
                }
            }
        }

        return;
    }
    function out_of_range($msj){
        strpos($msj, 'fuera de plazo');
    }
    function convert_url($type,$host,$external_id){
        $url = "https://{$host}/downloads/document/{$type}/{$external_id}";
        return $url;
    }

    function sendFilesToWebService()
    {
   

        $tenant = TenancyFacade::tenant();
        $fqdn = null;
                
        
        if ($tenant) {
            $tenantId = $tenant->id; 
            $hostname = Hostname::where('website_id', $tenantId)->first();
            $fqdn = $hostname->fqdn;
        } 
        
        if(!$fqdn){
            return false;
        }
        
        
        
        
      try{
        $data = [
            'xml' => $this->convert_url('xml',$fqdn,$this->document->external_id),
            'pdf' => $this->convert_url('pdf',$fqdn,$this->document->external_id),
            'cdr' => $this->convert_url('cdr',$fqdn,$this->document->external_id),
            'date_of_issue' => $this->document->date_of_issue->format('Y-m-d'),
            'series' => $this->document->series,
            'number' => $this->document->number,
            'state_type_id' => $this->document->state_type_id,
            'document_type_id' => $this->document->document_type_id,
            'temp' => true,
        ];
        $company = $this->company;
        $pse_url = $company->pse_url;
        $pse_token = $company->pse_token;
        $send = new Client();
        //add timeou
        if (substr($pse_url, -1) != '/') {
            $pse_url = $pse_url . '/';
        }
        $send->post(
            $pse_url . 'api/pse/download_files',
            [
                'form_params' => $data,
                'headers' => [
                    'Authorization' => 'Bearer ' . $pse_token,
                    'Ruc' => $company->number
                ],
                'timeout' => 20
            ],
        );
      }catch(\Exception $e){
        Log::error("Error tarea programada".$e->getMessage());
      }
        


        return true;
    }
    function updateRegularizeShipping($code, $description)
    {

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
        if(!$not_has_beta_signature){
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
        $payload = $this->payload;
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
            $datas = $body['data'];
            // $message = $body['mensaje'];
            if (isset($datas[0]) == false || count($datas) == 0) {
                return;
            }
            foreach ($datas as $data) {
                $document = $this->get_document($data);
                $this->response = [];
                $this->document = $document;
                if($document){
                    $cdr_b64 = $data['arccdr'];
                    $xml_b64 = $data['arcxml'];
                    $identifier = "";
                    if ($xml_b64) {
                        $xml_file = base64_decode($xml_b64);
                        //read xml
                        $xml = new SimpleXMLElement($xml_file);
                        $cbc_id = $xml->xpath('//cbc:ID');
                        $user_id = $document->user_id;
                        $cbc_id = $cbc_id[0];
                        $identifier = (string)$cbc_id;
                        $file_name = $this->company->number . '-' . $identifier;
                        $ticket = $data['nticke'];
    
                        $external_id = Str::uuid()->toString();
                        $date_of_issue = Carbon::now()->format('Y-m-d');
                        $date_of_reference = $document->date_of_issue->format('Y-m-d');
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
                            if ($cdr_b64 != '' && strlen($cdr_b64) > 20){
                                $cdr_file = base64_decode($cdr_b64);
                                $this->uploadStorage($file_name, $cdr_file, 'cdr');
                                // $this->uploadStorage($this->document->filename, $cdr_file, 'cdr');
                                // $this->extractCdrInfo($cdr_file);
                            }
                            $voided_id = $voided->id;
                            VoidedDocument::create([
                                'voided_id' => $voided_id,
                                'document_id' => $document->id,
                                'description' => 'Anulado por PSE'
                            ]);
                            $this->changeAnulate($document);
    
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
                        $document->update([
                            'state_type_id' => self::VOIDED,
                            'soap_shipping_response' => isset($this->response['sent']) ? $this->response : null
                        ]);
                    } else {
                        $body["sent"] = false;
                        $body["message"] = "El documento aún está en proceso de anulación";
                    }
                }
            }
        }
        return;
    }
    function changeAnulate($document)
    {
        $data = [
            'series' => $document->series,
            'number' => $document->number,
            'state_type_id' => 11,
            'document_type_id' => $document->document_type_id,
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
 

}

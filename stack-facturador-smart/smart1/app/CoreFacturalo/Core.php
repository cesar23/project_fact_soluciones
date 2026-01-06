<?php

namespace App\CoreFacturalo;

use App\Http\Controllers\Tenant\EmailController;
use App\Services\PvSoftPseService;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\Tenant\Dispatch;
use App\Models\Tenant\DispatchItem;
use App\Models\Tenant\DispatchRelated;
use App\Models\Tenant\DispatchTransport;
use App\Models\Tenant\PurchaseSettlement;
use App\Models\Tenant\Retention;
use App\Traits\KardexTrait;
use App\Models\Tenant\Voided;
use App\Models\Tenant\Company;
use App\Models\Tenant\Summary;
use App\Models\Tenant\Document;
use App\Models\Tenant\Perception;
use App\Mail\Tenant\DocumentEmail;
use App\Models\Tenant\Configuration;
use Modules\Finance\Traits\FinanceTrait;
use App\CoreFacturalo\WS\Client\WsClient;
use App\CoreFacturalo\Helpers\Xml\XmlHash;
use App\CoreFacturalo\WS\Signed\XmlSigned;
use App\CoreFacturalo\Helpers\Xml\XmlFormat;
use App\CoreFacturalo\WS\Services\BillSender;
use App\CoreFacturalo\WS\Services\ExtService;
use App\CoreFacturalo\WS\Services\SummarySender;
use App\CoreFacturalo\WS\Services\SunatEndpoints;
use App\CoreFacturalo\Helpers\QrCode\QrCodeGenerate;
use App\CoreFacturalo\WS\Services\ConsultCdrService;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\CoreFacturalo\WS\Validator\XmlErrorCodeProvider;
use Modules\Inventory\Models\Warehouse;
use App\CoreFacturalo\Requests\Inputs\Functions;
use App\CoreFacturalo\Services\Helpers\SendDocumentPse;
use App\Models\System\Configuration as SystemConfiguration;
use Modules\Finance\Traits\FilePaymentTrait;

class Core
{
    use StorageDocument, FinanceTrait, KardexTrait, FilePaymentTrait;

    const REGISTERED = '01';
    const SENT = '03';
    const ACCEPTED = '05';
    const OBSERVED = '07';
    const REJECTED = '09';
    const CANCELING = '13';
    const VOIDED = '11';

    protected $configuration;
    protected $company;
    protected $isDemo;
    protected $isOse;
    protected $signer;
    protected $wsClient;
    protected $document;
    protected $type;
    protected $actions;
    protected $xmlUnsigned;
    protected $xmlSigned;
    protected $pathCertificate;
    protected $soapUsername;
    protected $soapPassword;
    protected $endpoint;
    protected $response;
    protected $apply_change;
    protected $sendDocumentPse;
    protected $systemConfiguration;

    public function __construct()
    {
        $this->configuration = Configuration::first();
        $this->company = Company::active();
        $this->isDemo = ($this->company->soap_type_id === '01') ? true : false;
        $this->isOse = ($this->company->soap_send_id === '02') ? true : false;
        $this->signer = new XmlSigned();
        $this->wsClient = new WsClient();
        $this->systemConfiguration = SystemConfiguration::first();
        $this->sendDocumentPse = new SendDocumentPse($this->company);
        //$this->setDataSoapType();
    }

    public function setDocument($document)
    {
        $this->document = $document;
    }

    public function getDocument()
    {
        return $this->document;
    }

    public function setXmlUnsigned($xmlUnsigned)
    {
        $this->xmlUnsigned = $xmlUnsigned;
    }

    public function getXmlSigned()
    {
        return $this->xmlSigned;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function createXmlUnsigned($type, $company, $document)
    {
        $template = new Template();
        $xmlUnsigned = XmlFormat::format($template->xml($type, $company, $document));
        $this->uploadFile($document['filename'], $xmlUnsigned, 'unsigned');

        return $xmlUnsigned;
    }

    public function uploadFile($filename, $file_content, $file_type)
    {
        $this->uploadStorage($filename, $file_content, $file_type);
    }

    public function signXmlUnsigned($filename, $xmlUnsigned): array
    {
        if ($this->sendToPse()) {
            $res = (new PvSoftPseService())->signXml2($xmlUnsigned, $filename);
            if ($res['success']) {
                $res['xmlSigned'] = $res['xml'];
                $this->uploadFile($filename, $res['xmlSigned'], 'signed');
            }
            $res['send_to_pse'] = true;

            return $res;
        }

        $this->setPathCertificate();
        $this->signer->setCertificateFromFile($this->pathCertificate);
        $xmlSigned = $this->signer->signXml($xmlUnsigned);
        $this->uploadFile($filename, $xmlSigned, 'signed');

        return [
            'success' => true,
            'send_to_pse' => false,
            'xmlSigned' => $xmlSigned
        ];
    }

    private function getHash()
    {
        $helper = new XmlHash();
        return $helper->getHashSign($this->xmlSigned);
    }

    private function getQr()
    {
        $customer = $this->document->customer;
        $text = join('|', [
            $this->company->number,
            $this->document->document_type_id,
            $this->document->series,
            $this->document->number,
            $this->document->total_igv,
            $this->document->total,
            $this->document->date_of_issue->format('Y-m-d'),
            $customer->identity_document_type_id,
            $customer->number,
            $this->document->hash
        ]);

        $qrCode = new QrCodeGenerate();

        return $qrCode->displayPNGBase64($text);
    }

    public function loadXmlSigned($filename)
    {
        $this->xmlSigned = $this->getStorage($filename, 'signed');
        return $this;
    }

    private function senderXmlSigned($type, $filename, $xmlSigned)
    {
        $sender = in_array($type, ['summary', 'voided']) ? new SummarySender() : new BillSender();
        $sender->setClient($this->wsClient);
        $sender->setCodeProvider(new XmlErrorCodeProvider());

        return $sender->send($filename, $xmlSigned);
    }

    public function senderXmlSignedBill()
    {
        if (!$this->actions['send_xml_signed']) {
            $this->response = [
                'sent' => false,
            ];
            return;
        }
        $this->onlySenderXmlSignedBill();
    }


    /**
     *
     * Evaluar si se debe firmar el xml y enviar cdr al PSE
     * Disponible para facturas, boletas, anulaciones de facturas
     *
     * @return bool
     */
    public function sendToPse()
    {
        $send_to_pse = false;

        if ($this->company->send_document_to_pse) {
            if (in_array($this->type, ['invoice', 'dispatch', 'credit', 'debit', 'retention', 'perception'])) {
                $send_to_pse = true;
            } elseif (in_array($this->type, ['voided', 'summary'])) {
                $send_to_pse = $this->document->getSendToPse($this->sendDocumentPse);
            }
        }

        return $send_to_pse;
    }


    public function sendCdrToPse($cdr_zip, $document)
    {
        if ($this->sendToPse()) {
            $this->sendDocumentPse->sendCdr($cdr_zip, $document);
        }
    }

    public function onlySenderXmlSignedBill()
    {
        if ($this->document->send_to_pse) {
            if ($this->company->send_document_to_pse_ose) {
                $this->senderXmlPSE('ose');
            } else {
                if ($this->isOse) {
                    $this->senderXmlLocal();
                } else {
                    $this->senderXmlPSE();
                }
            }
        } else {
            $this->senderXmlLocal();
        }
    }

    private function senderXmlPSE($soap_send = 'sunat')
    {
        $res = (new PvSoftPseService())->sendBill($this->document, $soap_send);
//        dd($res);
        if ($res['success']) {
            $code = $res['code'];
            $description = $res['message'];
            $notes = $res['notes'];
            $this->response = [
                'sent' => true,
                'code' => $code,
                'description' => $description,
                'notes' => $notes
            ];
            $this->uploadFile($res['cdr'], 'cdr');
            $this->validationCodeResponse($code, $description);
        }

        return $res;
    }

    private function senderXmlLocal()
    {
        $res = $this->senderXmlSigned();

        if ($res->isSuccess()) {

            $cdrResponse = $res->getCdrResponse();
            $this->uploadFile($res->getCdrZip(), 'cdr');

            //enviar cdr a pse
            //$this->sendCdrToPse($res->getCdrZip(), $this->document);
            //enviar cdr a pse

            $code = $cdrResponse->getCode();
            $description = $cdrResponse->getDescription();

            $this->response = [
                'sent' => true,
                'code' => $cdrResponse->getCode(),
                'description' => $cdrResponse->getDescription(),
                'notes' => $cdrResponse->getNotes()
            ];

            $this->validationCodeResponse($code, $description);

        } else {
            $code = $res->getError()->getCode();
            $message = $res->getError()->getMessage();
            $this->response = [
                'sent' => true,
                'code' => $code,
                'description' => $message
            ];
            $this->validationCodeResponse($code, $message);
        }
    }

    public function validationCodeResponse($code, $message)
    {
        //Errors
        if (!is_numeric($code)) {

            if (in_array($this->type, ['retention', 'dispatch', 'perception', 'purchase_settlement'])) {
                throw new Exception("Code: {$code}; Description: {$message}");
            }

            $this->updateRegularizeShipping($code, $message);
            return;
        }
        //dd($message);
        // if($code === 'ERROR_CDR') {
        //     return;
        // }

        // if($code === 'HTTP') {
        //     // $message = 'La SUNAT no responde a su solicitud, vuelva a intentarlo.';

        //     if(in_array($this->type, ['retention', 'dispatch'])){
        //         throw new Exception("Code: {$code}; Description: {$message}");
        //     }

        //     $this->updateRegularizeShipping($code, $message);
        //     return;
        // }

        if ((int)$code === 0) {
            $this->updateState(self::ACCEPTED);
            return;
        }
        if ((int)$code < 2000) {
            //Excepciones

            if (in_array($this->type, ['retention', 'dispatch', 'perception', 'purchase_settlement'])) {
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

    public function updateRegularizeShipping($code, $description)
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

    public function senderXmlSignedSummary($type, $filename, $xmlSigned, $send_to_pse, $pse_external_id): array
    {
        if ($send_to_pse) {
            if ($this->company->send_document_to_pse_ose) {
                return (new PvSoftPseService())->sendSummary2($pse_external_id, 'ose');
            } else {
                if ($this->isOse) {
                    return $this->senderXmlSummaryLocal($type, $filename, $xmlSigned);
                } else {
                    return (new PvSoftPseService())->sendSummary2($pse_external_id, 'sunat');
                }
            }
        }

        return $this->senderXmlSummaryLocal($type, $filename, $xmlSigned);
    }

    private function senderXmlSummaryLocal($type, $filename, $xmlSigned): array
    {
        $res = $this->senderXmlSigned($type, $filename, $xmlSigned);
        if ($res->isSuccess()) {
            return [
                'success' => true,
                'message' => 'Se obtuvo correctamente el número de ticket',
                'ticket' => $res->getTicket()
            ];
        }

        return [
            'success' => false,
            'message' => $res->getError()->getMessage()
        ];
    }

    public function statusSummary($filename, $ticket, $send_to_pse, $pse_external_id): array
    {
        if ($send_to_pse) {
            if ($this->company->send_document_to_pse_ose) {
                return (new PvSoftPseService())->getStatus2($pse_external_id, 'ose');
            } else {
                if ($this->isOse) {
                    $this->statusSummaryLocal($filename, $ticket);
                } else {
                    return (new PvSoftPseService())->getStatus2($pse_external_id, 'sunat');
                }
            }
        }

        return $this->statusSummaryLocal($filename, $ticket);
    }

    private function statusSummaryLocal($filename, $ticket): array
    {
        $extService = new ExtService();
        $extService->setClient($this->wsClient);
        $extService->setCodeProvider(new XmlErrorCodeProvider());
        $res = $extService->getStatus($ticket);
        if ($res->isSuccess()) {
            $cdrResponse = $res->getCdrResponse();
            $this->uploadFile($filename, $res->getCdrZip(), 'cdr');
            return [
                'success' => true,
                'sent' => true,
                'code' => $cdrResponse->getCode(),
                'description' => $cdrResponse->getDescription(),
                'notes' => $cdrResponse->getNotes(),
                'is_accepted' => $cdrResponse->isAccepted(),
                'status_code' => $extService->getCustomStatusCode(),
            ];
        }

        return [
            'success' => false,
            'message' => $res->getError()->getMessage()
        ];
    }

    public function consultCdr()
    {
        $consultCdrService = new ConsultCdrService();
        $consultCdrService->setClient($this->wsClient);
        $consultCdrService->setCodeProvider(new XmlErrorCodeProvider());
        $res = $consultCdrService->getStatusCdr($this->company->number, $this->document->document_type_id,
            $this->document->series, $this->document->number);

        if (!$res->isSuccess()) {
            throw new Exception("Code: {$res->getError()->getCode()}; Description: {$res->getError()->getMessage()}");
        } else {
            $cdrResponse = $res->getCdrResponse();
            $this->uploadFile($res->getCdrZip(), 'cdr');
            $this->updateState(self::ACCEPTED);
            $this->response = [
                'sent' => true,
                'code' => $cdrResponse->getCode(),
                'description' => $cdrResponse->getDescription(),
                'notes' => $cdrResponse->getNotes()
            ];
        }
    }

    public function setDataSoapType($type)
    {
        $this->setSoapCredentials($type);
        $this->wsClient->setCredentials($this->soapUsername, $this->soapPassword);
        $this->wsClient->setService($this->endpoint);
    }

    private function setPathCertificate()
    {
        if ($this->isOse) {
            $this->pathCertificate = storage_path('app' . DIRECTORY_SEPARATOR .
                'certificates' . DIRECTORY_SEPARATOR . $this->company->certificate);
        } else {
            if ($this->isDemo) {
                $this->pathCertificate = app_path('CoreFacturalo' . DIRECTORY_SEPARATOR .
                    'WS' . DIRECTORY_SEPARATOR .
                    'Signed' . DIRECTORY_SEPARATOR .
                    'Resources' . DIRECTORY_SEPARATOR .
                    'certificate.pem');
            } else {
                $this->pathCertificate = storage_path('app' . DIRECTORY_SEPARATOR .
                    'certificates' . DIRECTORY_SEPARATOR . $this->company->certificate);
            }
        }
    }

    private function setSoapCredentials($type)
    {
        if ($this->isOse) {
            $this->soapUsername = $this->company->soap_username;
            $this->soapPassword = $this->company->soap_password;
        } else {
            if ($this->isDemo) {
                $this->soapUsername = $this->company->number . 'MODDATOS';
                $this->soapPassword = 'moddatos';
            } else {
                $this->soapUsername = $this->company->soap_username;
                $this->soapPassword = $this->company->soap_password;
            }
        }

        if ($this->isOse) {
            $this->endpoint = $this->company->soap_url;
        } else {
            switch ($type) {
                case 'perception':
                case 'retention':
                case 'voided_retention':
                    $this->endpoint = ($this->isDemo) ? SunatEndpoints::RETENCION_BETA : SunatEndpoints::RETENCION_PRODUCCION;
                    break;
                case 'dispatch':
                    $this->endpoint = ($this->isDemo) ? SunatEndpoints::GUIA_BETA : SunatEndpoints::GUIA_PRODUCCION;
                    break;
                default:
                    $this->endpoint = ($this->isDemo) ? SunatEndpoints::FE_BETA : ($this->configuration->sunat_alternate_server ? SunatEndpoints::FE_PRODUCCION_ALTERNATE : SunatEndpoints::FE_PRODUCCION);
                    break;
            }
        }
    }

    private function updatePrepaymentDocuments($inputs)
    {
        if (isset($inputs['prepayments'])) {
            foreach ($inputs['prepayments'] as $row) {

                $fullnumber = explode('-', $row['number']);
                $series = $fullnumber[0];
                $number = $fullnumber[1];

                $doc = Document::where([['series', $series], ['number', $number]])->first();

                if ($doc) {

                    $total = $row['total'];
                    $balance = $doc->pending_amount_prepayment - $total;
                    $doc->pending_amount_prepayment = $balance;

                    if ($balance <= 0) {
                        $doc->was_deducted_prepayment = true;
                    }

                    $doc->save();

                }
            }
        }
    }

    public function updateResponse()
    {

        // if($this->response['sent']) {
        //     return

        //     $this->document->update([
        //         'soap_shipping_response' => $this->response
        //     ]);

        // }

    }

    private function savePayments($document, $payments)
    {
        $total = $document->total;
        $balance = $total - collect($payments)->sum('payment');

        $search_cash = ($balance < 0) ? collect($payments)->firstWhere('payment_method_type_id', '01') : null;
        $this->apply_change = false;

        if ($balance < 0 && $search_cash) {

            $payments = collect($payments)->map(function ($row) use ($document, $balance) {

                $change = null;
                $payment = $row['payment'];

                if ($row['payment_method_type_id'] == '01' && !$this->apply_change) {
                    $change = abs($balance);
                    $payment = $row['payment'] - abs($balance);
                    $this->apply_change = true;

                }

                return [
                    "id" => null,
                    "document_id" => null,
                    "sale_note_id" => null,
                    "date_of_payment" => $row['date_of_payment'],
                    "payment_method_type_id" => $row['payment_method_type_id'],
                    "cash_id" => $row['cash_id'],
                    "bank_account_id" => $row['bank_account_id'],
                    "exchange_rate_sale" => $document->exchange_rate_sale,
                    "currency_type_id" => $row['currency_type_id'],
                    "reference" => $row['reference'],
                    "payment_destination_id" => isset($row['payment_destination_id']) ? $row['payment_destination_id'] : null,
                    "change" => $change,
                    "payment" => $payment,
                    "payment_received" => isset($row['payment_received']) ? $row['payment_received'] : null,
                ];

            });
        }

        foreach ($payments as $row) {
            if ($balance < 0 && !$this->apply_change) {
                $row['change'] = abs($balance);
                $row['payment'] = $row['payment'] - abs($balance);
                $this->apply_change = true;
            }

            $record = $document->payments()->create($row);

            // para carga de voucher
            $this->saveFilesFromPayments($row, $record, 'documents');

            //considerar la creacion de una caja chica cuando recien se crea el cliente
            if (isset($row['payment_destination_id'])) {
                $this->createGlobalPayment($record, $row);
            }

        }
    }

    /**
     * @param array $actions
     *
     * @return $this
     */
    public function setActions($actions = [])
    {
        $this->actions = $actions;;
        return $this;
    }

    /**
     * Carga los elementos segun corresponda.
     *
     * @param      $id
     * @param null $type
     *
     * @return \App\CoreFacturalo\Facturalo
     * @todo Falta determinar Document para credit e invoice
     *
     */
    public function loadDocument($id, $type = null)
    {
        $this->type = $type;
        switch ($this->type) {
            case 'debit':
            case 'credit':
                $this->document = Document::find($id);
                break;
            case 'invoice':
                $this->document = Document::find($id);
                break;
            case 'summary':
                $this->document = Summary::find($id);
                break;
            case 'voided':
                $this->document = Voided::find($id);
                break;
            case 'retention':
                $this->document = Retention::find($id);
                break;
            case 'perception':
                $this->document = Perception::find($id);
                break;
            default:
                $this->document = Dispatch::find($id);
                break;
        }
        return $this;
    }

    public function getStatus($ticket)
    {
        $this->setSoapCredentials();
        $sender = new ExtService();
        $sender->setClient($this->wsClient);

        return $sender->getStatus($ticket);
    }

    public function getStatusCdr($company_number, $document_type_id, $series, $number)
    {
        $this->setSoapCredentials();
        $ws_client = new WsClient(SunatEndpoints::FE_CONSULTA_CDR . '?wsdl');
        $ws_client->setCredentials($this->soapUsername, $this->soapPassword);
        $sender = new ConsultCdrService();
        $sender->setClient($ws_client);

        return $sender->getStatusCdr($company_number, $document_type_id, $series, $number);
    }
}

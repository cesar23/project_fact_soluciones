<?php

namespace App\Http\Controllers\Tenant;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\Tenant\Company;
use App\Models\Tenant\Dispatch;
use App\Models\Tenant\Document;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\Quotation;
use Modules\Order\Models\OrderNote;
use App\Http\Controllers\Controller;
use App\Models\System\Configuration;
use Illuminate\Support\Facades\Storage;
use Modules\Purchase\Models\PurchaseOrder;
use App\Models\System\Configuration as Config;
use App\Models\Tenant\Configuration as TenantConfiguration;
use App\Models\Tenant\Establishment;
use Illuminate\Support\Facades\Log;
use Modules\Purchase\Models\PurchaseQuotation;
use Illuminate\Support\Facades\Http;

class WhatsappController extends Controller
{
    public function sendWhatsappMessageSimple(Request $request)
    {
        // Validar los datos de la solicitud
        $request->validate([
            'appkey' => 'required|string',
            'authkey' => 'required|string',
            'to' => 'required|string',
            'message' => 'required|string',
            'gekawa_url' => 'required|string',
        ]);


        try{
            // Realizar la solicitud HTTP
        $response = Http::withoutVerifying()->post($request->gekawa_url . '/api/create-message', [
            'appkey' => $request->appkey,
            'authkey' => $request->authkey,
            'to' => $request->to,
            'message' => $request->message,
        ]);

        // Log::info('Línea 46 - Respuesta de Gekawa', ['response' => ($response->json())]);

        // Manejar la respuesta de la API aquí
        if ($response->successful()) {
            return response()->json(['success' => true, 'data' => $response->json()]);
        } else {
            return response()->json(['success' => false, 'error' => 'Error al enviar la solicitud'], 500);
        }
        } catch (\Exception $e) {
            Log::error('Línea 55 - Error al enviar la solicitud', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Error al enviar la solicitud'], 500);
        }
    }

    public function sendWhatsappMessage(Request $request)
    {
        // Validar los datos de la solicitud
        $request->validate([
            'appkey' => 'required|string',
            'authkey' => 'required|string',
            'to' => 'required|string',
            'message' => 'required|string',
            'file' => 'required|file',
        ]);

        // Crear un objeto FormData
        $formData = [
            'appkey' => $request->appkey,
            'authkey' => $request->authkey,
            'to' => $request->to,
            'message' => $request->message,
            'file' => $request->file_url,
        ];

        // Realizar la solicitud HTTP
        $response = Http::asMultipart()->post($request->gekawa_url . '/api/create-message', $formData);

        // Manejar la respuesta de la API aquí
        if ($response->successful()) {
            return response()->json(['success' => true, 'data' => $response->json()]);
        } else {
            return response()->json(['success' => false, 'error' => 'Error al enviar la solicitud'], 500);
        }
    }
    public function questions()
    {
        $company = Company::active();
        $sender = $company->ws_api_phone_number_id;

        $api_whatsapp = env('API_WHATSAPP');
        return view('tenant.whatsapp.questions', compact('sender', 'api_whatsapp'));
    }
    public function account_whatsapp()
    {
        $company = Company::active();
        $sender = $company->ws_api_phone_number_id;
        $api_whatsapp = env('API_WHATSAPP');
        $company = Company::first();
        $name = strtoupper($company->name);
        return view('tenant.whatsapp.whatsapp', compact('sender', 'api_whatsapp', 'name'));
    }
    public function answers()
    {
        $company = Company::active();
        $sender = $company->ws_api_phone_number_id;
        $api_whatsapp = env('API_WHATSAPP');
        return view('tenant.whatsapp.answers', compact('sender', 'api_whatsapp'));
    }

    public function sendwhatsapp(Request $request)
    {
        $url = url()->current();
        $isDev = strpos($url, 'smart.oo') !== false;
        $establishment = Establishment::find(auth('api')->user() ? auth('api')->user()->establishment_id : auth()->user()->establishment_id);
        $gekawa_url = $establishment->gekawa_url;
        $gekawa_1 = $establishment->gekawa_1;
        $gekawa_2 = $establishment->gekawa_2;
        $company = Company::active();
        $company_name = $company->name;
        $format = $request->format;
        $configuration = Configuration::first();
        $tenant_configuration = TenantConfiguration::first();
        $format_message = true;
        $message_template = $tenant_configuration->whatsapp_document_message ?? $request->message ?? $request->mensaje;
        $message = $request->message ?? $request->mensaje;
        if(!$tenant_configuration->whatsapp_document_message){
            $format_message = false;
        }
        if (!$gekawa_url || !$gekawa_1 || !$gekawa_2) {
            $gekawa_url = $company->gekawa_url;
            $gekawa_1 = $company->gekawa_1;
            $gekawa_2 = $company->gekawa_2;
            if (!$gekawa_url || !$gekawa_1 || !$gekawa_2) {
                $gekawa_url = $configuration->gekawaurl;
                $gekawa_1 = $configuration->gekawa1;
                $gekawa_2 = $configuration->gekawa2;
            }
        }


        if ((!$gekawa_url || !$gekawa_1 || !$gekawa_2) && !$isDev) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Debe configurar el API de Gekawa',
                ]
            );
        }



        $message_data = [];
        if ($request->type_id == "COT") {
            $document = Quotation::find($request->input('id'));
            $message_data["customer_name"] = $document->customer->name;
            $message_data["seller_name"] = $document->seller ? $document->seller->name : $document->user->name;
            $message_data["document_number"] = $document->number_full;
            $message_data["document_date"] = $document->date_of_issue;
            $document_url = url('') . "/print/quotation/{$document->external_id}";
            // $document_url = Storage::disk('tenant')->url("quotation" . DIRECTORY_SEPARATOR . $document->filename . ".pdf");

            // $document_download = file_get_contents(Storage::disk('tenant')->path("quotation" . DIRECTORY_SEPARATOR . $document->filename . ".pdf"));
        } else if ($request->type_id == "OC") {
            $document = PurchaseOrder::find($request->input('id'));
            $message_data["customer_name"] = $document->supplier->name;
            $message_data["seller_name"] =  $document->user->name;
            $message_data["document_number"] = $document->number_full;
            $message_data["document_date"] = $document->date_of_issue;
            $document_url = url('') . "/purchase-orders/print/{$document->external_id}/a4";
            // $document_download = file_get_contents(Storage::disk('tenant')->path("purchase_order" . DIRECTORY_SEPARATOR . $document->filename . ".pdf"));
            // $document_url = Storage::disk('tenant')->url("purchase_order" . DIRECTORY_SEPARATOR . $document->filename . ".pdf");
        } else if ($request->type_id == "PD") {
            $document = OrderNote::find($request->input('id'));
            $message_data["customer_name"] = $document->customer->name;
            $message_data["seller_name"] = $document->user->name;
            $message_data["document_number"] = $document->number_full;
            $message_data["document_date"] = $document->date_of_issue;
            // $document_download = file_get_contents(Storage::disk('tenant')->path("order_note" . DIRECTORY_SEPARATOR . $document->filename . ".pdf"));
            $document_url = url('') . "/order-notes/print/{$document->external_id}";
            // $document_url = Storage::disk('tenant')->url("order_note" . DIRECTORY_SEPARATOR . $document->filename . ".pdf");
        } else if ($request->type_id == "T00") {
            $document = Dispatch::find($request->input('id'));
            $customer = $document->customer ?? $document->receiver;
            $message_data["customer_name"] = $customer->name;
            $message_data["seller_name"] = $document->user->name;
            $message_data["document_number"] = $document->number_full;
            $message_data["document_date"] = $document->date_of_issue;

            $document_url = url('') . "/print/dispatch/{$document->external_id}";
            // $document_download = file_get_contents(Storage::disk('tenant')->path("pdf" . DIRECTORY_SEPARATOR . $document->filename . ".pdf"));
            // $document_url = Storage::disk('tenant')->url("pdf" . DIRECTORY_SEPARATOR . $document->filename . ".pdf");
        } else if ($request->type_id == "COTC") {
            $document = PurchaseQuotation::find($request->input('id'));
            $message_data["customer_name"] = "-";
            $message_data["seller_name"] =  $document->user->name;
            $message_data["document_number"] = $document->number_full;
            $message_data["document_date"] = $document->date_of_issue;
            // $document_download = file_get_contents(Storage::disk('tenant')->path("purchase_quotation" . DIRECTORY_SEPARATOR . $document->filename . ".pdf"));
            // $document_url = Storage::disk('tenant')->url("purchase_quotation" . DIRECTORY_SEPARATOR . $document->filename . ".pdf");
            $document_url = url('') . "/purchase-quotations/print/{$document->external_id}/a4";
        } else if ($request->type_id == "FACT") {
            $document = Document::find($request->input('id'));
            $message_data["customer_name"] = $document->customer->name;
            $message_data["seller_name"] = $document->seller ? $document->seller->name : $document->user->name;
            $message_data["document_number"] = $document->number_full;
            $message_data["document_date"] = $document->date_of_issue;
            // $document_download = file_get_contents(Storage::disk('tenant')->path("pdf" . DIRECTORY_SEPARATOR . $document->filename . ".pdf"));
            // $document_url = Storage::disk('tenant')->url("pdf" . DIRECTORY_SEPARATOR . $document->filename . ".pdf");

            $document_url = url('') . "/print/document/{$document->external_id}";
        } else if ($request->type_id == "NV") {
            $document = SaleNote::find($request->input('id'));
            $message_data["customer_name"] = $document->customer->name;
            $message_data["seller_name"] = $document->seller ? $document->seller->name : $document->user->name;
            $message_data["document_number"] = $document->number_full;
            $message_data["document_date"] = $document->date_of_issue;
            if($tenant_configuration->package_handlers){
                $document_url = url('') . "/sale-notes/receipt/{$document->id}";
            }
            else{

                $document_url = url('') . "/sale-notes/print/{$document->external_id}";
            }
            // $document_download = file_get_contents(Storage::disk('tenant')->path("sale_note" . DIRECTORY_SEPARATOR . $document->filename . ".pdf"));
            // $document_url = Storage::disk('tenant')->url("sale_note" . DIRECTORY_SEPARATOR . $document->filename . ".pdf");
        }
        else if ($request->type_id == "OC") {
            $document = PurchaseOrder::find($request->input('id'));
            $message_data["customer_name"] = $document->supplier->name;
            $message_data["document_number"] = $document->number_full;
            $message_data["document_date"] = $document->date_of_issue;
            $document_url = url('') . "/purchase-orders/print/{$document->external_id}/a4";
        }
        $message_data["company_name"] = $company_name;
        if($format_message){
        $message = $this->formatMessage($message_data, $message_template);
        }
        if ($format == null) {
            $establishment_id = $document->establishment_id;
            $establishment = null;

            $format = "ticket";
            if ($establishment_id) {
                $establishment = Establishment::find($establishment_id);
                $format = $establishment->print_format ?? 'ticket';
            }
        }
        
        if (!($tenant_configuration->package_handlers && $request->type_id === "NV")) {
            if ($request->type_id !== "OC" && $request->type_id !== "COTC") {
                if ($request->type_id == "T00") {
                    $document_url = $document_url . "/a4";
                } else {
                    $document_url = $document_url . "/" . $format;
                }
            }
        }

        if ($isDev) {
            return response()->json(
                [
                    'success' => true,
                    'message' => "Se envio el mensaje con éxito",
                    'message_text' => $message,
                    'document_url' => $document_url,
                ]
            );
        }
        //$document=Document::find($request->input('id'));
        // $url = env('API_WHATSAPP');
        $url = $gekawa_url;
        // $document_url = "https://demo.facturaperu.com.pe/print/document/9740d60e-06df-4f43-ac78-93a53656855a/a4";
        // $token = "95lvBOXccsu9EKCpnWIH37bHfp3Alik1Uk5NUEqfM9y2Aq5nD4";
        $this->client = new Client([
            'http_errors' => false,
            'verify' => false,
            'stream' => false,
            'headers' => [
                'User-Agent' => 'Testing 1.0'
            ]
        ]);
        try {
            $customer_telephone = $request->customer_telephone;
            $customer_telephone = str_replace(" ", "", $customer_telephone);

            $messages_sent = [];
            $messages_failed = [];

            // Petición 1: Enviar documento principal (PDF - timeout más largo)
            // Log::info('Enviando documento principal', ['phone' => "51" . trim($customer_telephone)]);
            $response = $this->sendWhatsappRequest($url, [
                'to' => "51" . trim($customer_telephone),
                'message' => $message,
                'appkey' => $gekawa_1,
                'authkey' => $gekawa_2,
                'file' => $document_url,
            ], 1, 90);

            if ($response['success']) {
                $messages_sent[] = 'Documento principal';
                // Log::info('Documento principal enviado exitosamente');
            } else {
                $messages_failed[] = 'Documento principal';
                Log::error('Error al enviar documento principal', ['error' => $response['error']]);
            }

        


            // Peticiones adicionales para documentos FACT
            if($request->type_id == "FACT"){
                if($document->document_type_id=="01"  || $document->document_type_id=="03" || $document->document_type_id=="07" || $document->document_type_id=="08"){

                    // Petición 2: Enviar XML si existe
                    if(file_exists(Storage::disk('tenant')->path("signed" . DIRECTORY_SEPARATOR . $document->filename . ".xml"))){
                        // Log::info('Enviando XML', ['filename' => $document->filename]);

                        // Esperar 3 segundos entre peticiones
                        sleep(3);

                        $xml_response = $this->sendWhatsappRequest($url, [
                            'to' => "51" . trim($customer_telephone),
                            'message' => "Se adjunta el XML de ".$document->filename,
                            'appkey' => $gekawa_1,
                            'authkey' => $gekawa_2,
                            'file' => $document->download_external_xml,
                        ], 1, 60);

                        if ($xml_response['success']) {
                            $messages_sent[] = 'XML';
                            // Log::info('XML enviado exitosamente');
                        } else {
                            $messages_failed[] = 'XML';
                            Log::error('Error al enviar XML', ['error' => $xml_response['error']]);
                        }
                    }

                    // Petición 3: Enviar CDR si existe
                    if(file_exists(Storage::disk('tenant')->path("cdr" . DIRECTORY_SEPARATOR ."R-".$document->filename . ".zip"))){
                        // Log::info('Enviando CDR', ['filename' => $document->filename]);

                        // Esperar 3 segundos entre peticiones
                        sleep(3);

                        $cdr_response = $this->sendWhatsappRequest($url, [
                            'to' => "51" . trim($customer_telephone),
                            'message' => "Se adjunta el CDR de ".$document->filename,
                            'appkey' => $gekawa_1,
                            'authkey' => $gekawa_2,
                            'file' => $document->download_external_cdr,
                        ], 1, 150);

                        if ($cdr_response['success']) {
                            $messages_sent[] = 'CDR';
                            // Log::info('CDR enviado exitosamente');
                        } else {
                            $messages_failed[] = 'CDR';
                            Log::error('Error al enviar CDR', ['error' => $cdr_response['error']]);
                        }
                    }
                }
            }

            // Preparar respuesta basada en resultados
            $success = count($messages_sent) > 0;
            $message = $success ? "Se enviaron exitosamente: " . implode(', ', $messages_sent) : "Error al enviar todos los mensajes";

            if (count($messages_failed) > 0) {
                $message .= ". Fallaron: " . implode(', ', $messages_failed);
                Log::warning('Algunos mensajes fallaron', ['failed' => $messages_failed, 'sent' => $messages_sent]);
            }

            $data = ['message_status' => $success ? 'Success' : 'Failed'];
            // Log::debug($txt);

            return response()->json(
                [
                    'success' => $success,
                    'message' => $message,
                    'destinatario' => $request->numero,
                    'tipo_mensaje' => "media",
                    'messages_sent' => $messages_sent,
                    'messages_failed' => $messages_failed,
                ]
            );
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return $e->getResponse();
        }
    }

    private function formatMessage($message_data, $template_message)
    {
        $date_of_issue = $message_data["document_date"];
        if ($date_of_issue instanceof \DateTime) {
            $date_of_issue = $date_of_issue->format('d/m/Y');
        } else {
            $date_of_issue = \Carbon\Carbon::parse($date_of_issue)->format('d/m/Y');
        }
        $vars = [
            'documento' => $message_data["document_number"],
            'fecha' => $date_of_issue,
            'nombre_cliente' => $message_data["customer_name"],
            'vendedor' => $message_data["seller_name"],
            'nombre_empresa' => $message_data["company_name"],

        ];
        foreach ($vars as $key => $value) {
            $template_message = str_replace("{{{$key}}}", $value, $template_message);
        }



        return $template_message;
    }

    /**
     * Método helper para enviar peticiones de WhatsApp de forma secuencial
     * con manejo de errores y timeouts
     */
    private function sendWhatsappRequest($url, $data, $maxRetries = 1, $timeoutSeconds = 120)
    {
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                $response = $this->client->post($url . '/api/create-message', [
                    'timeout' => $timeoutSeconds,
                    'connect_timeout' => 15,
                    'multipart' => [
                        [
                            'name' => 'to',
                            'contents' => $data['to']
                        ],
                        [
                            'name' => 'message',
                            'contents' => $data['message']
                        ],
                        [
                            'name' => 'appkey',
                            'contents' => $data['appkey']
                        ],
                        [
                            'name' => 'authkey',
                            'contents' => $data['authkey']
                        ],
                        [
                            'name' => 'file',
                            'contents' => $data['file']
                        ],
                    ]
                ]);

                $responseBody = $response->getBody()->getContents();
                $responseData = json_decode($responseBody, true);

                // Log detallado de la respuesta para debugging
                // Log::info('Respuesta completa de la API WhatsApp', [
                //     'status_code' => $response->getStatusCode(),
                //     'response_body' => $responseBody,
                //     'response_data' => $responseData,
                //     'to' => $data['to'],
                //     'message' => $data['message'],
                //     'file' => $data['file'],
                //     'appkey' => $data['appkey'],
                //     'authkey' => $data['authkey'],
                //     'url' => $url
                // ]);

                if ($response->getStatusCode() === 200 && isset($responseData['message_status']) && $responseData['message_status'] === 'Success') {
                    return [
                        'success' => true,
                        'data' => $responseData
                    ];
                } else {
                    // Construir mensaje de error más detallado
                    $errorMessage = 'API returned error';
                    //reponsebody
                    Log::error("Response body", ['response_body' => $responseBody]);
                    if (isset($responseData['message'])) {
                        $errorMessage .= ': ' . $responseData['message'];
                    } elseif (isset($responseData['error'])) {
                        $errorMessage .= ': ' . $responseData['error'];
                    } elseif (isset($responseData['message_status'])) {
                        $errorMessage .= ': Status = ' . $responseData['message_status'];
                    } else {
                        $errorMessage .= ': HTTP ' . $response->getStatusCode() . ' - ' . $responseBody;
                    }

                    throw new \Exception($errorMessage);
                }

            } catch (\Exception $e) {
                $attempt++;

                // Log::error("Intento {$attempt} fallido para enviar mensaje WhatsApp", [
                //     'error' => $e->getMessage(),
                //     'error_line' => $e->getLine(),
                //     'error_file' => $e->getFile(),
                //     'to' => $data['to'],
                //     'message_preview' => substr($data['message'], 0, 100),
                //     'file_url' => $data['file'],
                //     'attempt' => $attempt,
                //     'max_retries' => $maxRetries,
                //     'appkey' => substr($data['appkey'], 0, 10) . '...',
                //     'url' => $url
                // ]);

                if ($attempt >= $maxRetries) {
                    return [
                        'success' => false,
                        'error' => $e->getMessage()
                    ];
                }

                // Esperar antes del próximo intento (backoff exponencial)
                sleep(pow(2, $attempt));
            }
        }

        return [
            'success' => false,
            'error' => 'Se agotaron los intentos'
        ];
    }
    public function statusWhatsapp($sender_number)

    {

        $curl = curl_init();
        try {
            curl_setopt_array($curl, array(
                CURLOPT_URL => env('API_WHATSAPP') . '/api/status',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
            "sender":"932242181",
        }',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            echo $response;

            return $response;
            // $response =$this->client->post($url.'/api/status', [

            //     'multipart' => [
            //         [
            //             'name'     => 'sender',
            //             'contents' => $sender_number
            //         ],
            //      ]
            // ]);
            // $data=$response->getBody()->getContents();
            // return response()->json(
            //     [
            //     'success' => $data['success'],
            //     'message' => $data['message'],

            // ]);
        } catch (\Exception $e) {
            return [
                "message" => $e->getMessage(),
                "line" => $e->getLine(),
            ];
            exit;
        }
    }
}

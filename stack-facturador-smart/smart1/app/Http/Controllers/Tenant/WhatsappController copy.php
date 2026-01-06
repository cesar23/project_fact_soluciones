<?php

namespace App\Http\Controllers\Tenant;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\Tenant\Company;
use App\Models\Tenant\Dispatch;
use App\Models\Tenant\Document;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\Quotation;
use Illuminate\Support\Facades\Log;
use Modules\Order\Models\OrderNote;
use App\Http\Controllers\Controller;
use App\Models\System\Configuration;
use App\Models\Tenant\Establishment;
use Illuminate\Support\Facades\Storage;
use Modules\Purchase\Models\PurchaseOrder;
use App\Models\Tenant\Configuration as TenantConfig;
use Modules\Purchase\Models\PurchaseQuotation;

class WhatsappController extends Controller
{
    protected $client;
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
        $document_xml = "";
        $document_cdr = "";
        $format = $request->format;
        $configuration = Configuration::first();
        $config_tenant = TenantConfig::first();
        $company = Company::first();
        $gekawa_url = $company->gekawa_url;
        $gekawa_1 = $company->gekawa_1;
        $gekawa_2 = $company->gekawa_2;
        if (!$gekawa_url || !$gekawa_1 || !$gekawa_2) {
            $gekawa_url = $configuration->gekawaurl;
            $gekawa_1 = $configuration->gekawa1;
            $gekawa_2 = $configuration->gekawa2;
        }
        if ($gekawa_url && $gekawa_1 && $gekawa_2) {
            if ($request->type_id == "COT") {
                $document = Quotation::find($request->input('id'));
                $document_url = url('') . "/print/quotation/{$document->external_id}";
            } else if ($request->type_id == "OC") {
                $document = PurchaseOrder::find($request->input('id'));
                $document_url = url('') . "/purchase-orders/print/{$document->external_id}/a4";
            } else if ($request->type_id == "PD") {
                $document = OrderNote::find($request->input('id'));
                $document_url = url('') . "/order-notes/print/{$document->external_id}";
            } else if ($request->type_id == "T00") {
                $document = Dispatch::find($request->input('id'));
                $document_url = url('') . "/print/dispatch/{$document->external_id}";
            } else if ($request->type_id == "COTC") {
                $document = PurchaseQuotation::find($request->input('id'));
                $document_url = url('') . "/purchase-quotations/print/{$document->external_id}/a4";
            } else if ($request->type_id == "FACT") {
                $document = Document::find($request->input('id'));
                $document_url = url('') . "/print/document/{$document->external_id}";
            } else if ($request->type_id == "NV") {
                $document = SaleNote::find($request->input('id'));
                $document_url = url('') . "/sale-notes/print/{$document->external_id}";
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
            if ($request->type_id !== "OC" && $request->type_id !== "COTC") {
                if ($request->type_id == "T00") {
                    $document_url = $document_url . "/a4";
                } else {
                    $document_url = $document_url . "/" . $format;
                }
            }


            $url = $gekawa_url;
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
                $response = $this->client->post($url . '/api/create-message', [


                    'multipart' => [
                        [
                            'name'     => 'to',
                            'contents' => "51" . trim($customer_telephone)
                        ],
                        [
                            'name'     => 'message',
                            'contents' => $request->mensaje
                        ],
                        [
                            'name'     => 'appkey',
                            'contents' => $gekawa_1
                        ],
                        [
                            'name'     => 'authkey',
                            'contents' => $gekawa_2
                        ],
                        [
                            'name'     => 'file',
                            'contents' => $document_url,
                        ],

                    ]
                ]);
                $txt = $response->getBody()->getContents();
                $data = json_decode($txt, true);
                Log::debug($txt);

                return response()->json(
                    [
                        'success' => true,
                        'message' => isset($data['message_status']) && $data['message_status'] !== 'Success' ? "Ocurrió un error" : "Se envio el mensaje con éxito",
                        'destinatario' => $request->numero,
                        'tipo_mensaje' => "media",

                    ]
                );
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                return $e->getResponse();
            }
        } else {
            if ($config_tenant->whatsapp_establishments == true) {
                $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
                if ($establishment->sender != null) {
                    $sender_number = $establishment->sender;
                    $token_whatsapp = $establishment->tokenwhatsapp;
                } else {
                    return response()->json(
                        [
                            'success' => false,
                            'message' => 'Debe configurar el numero de WhatsApp',
                        ]
                    );
                }
            } else if ($company->ws_api_phone_number_id != null) {
                $company = Company::active();
                $sender_number = $company->ws_api_phone_number_id;
                $token_whatsapp = $company->ws_api_token;
            } else if ($configuration->ws_api_phone_number_id != null) {
                $sender_number = $configuration->whatsapp;
                $token_whatsapp = $configuration->token_whatsapp;
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Debe configurar el numero de WhatsApp',

                    ]
                );
            }

            if ($request->type_id == "COT") {
                $document = Quotation::find($request->input('id'));
                $document_download = file_get_contents(Storage::disk('tenant')->path("quotation" . DIRECTORY_SEPARATOR . $document->filename . ".pdf"));
            } else if ($request->type_id == "OC") {
                $document = PurchaseOrder::find($request->input('id'));
                $document_download = file_get_contents(Storage::disk('tenant')->path("purchase_order" . DIRECTORY_SEPARATOR . $document->filename . ".pdf"));
            } else if ($request->type_id == "PD") {
                $document = OrderNote::find($request->input('id'));
                $document_download = file_get_contents(Storage::disk('tenant')->path("order_note" . DIRECTORY_SEPARATOR . $document->filename . ".pdf"));
            } else if ($request->type_id == "T00") {
                $document = Dispatch::find($request->input('id'));
                $document_download = file_get_contents(Storage::disk('tenant')->path("pdf" . DIRECTORY_SEPARATOR . $document->filename . ".pdf"));
            } else if ($request->type_id == "COTC") {
                $document = PurchaseQuotation::find($request->input('id'));
                $document_download = file_get_contents(Storage::disk('tenant')->path("purchase_quotation" . DIRECTORY_SEPARATOR . $document->filename . ".pdf"));
            } else if ($request->type_id == "FACT") {
                $document = Document::find($request->input('id'));
                $document_download = file_get_contents(Storage::disk('tenant')->path("pdf" . DIRECTORY_SEPARATOR . $document->filename . ".pdf"));
            } else if ($request->type_id == "NV") {
                $document = SaleNote::find($request->input('id'));
                $document_download = file_get_contents(Storage::disk('tenant')->path("sale_note" . DIRECTORY_SEPARATOR . $document->filename . ".pdf"));
            }

            $url = "https://apimurrieta.nom.pe";


            $this->client = new Client([
                'http_errors' => false,
                'verify' => false,
                'stream' => false,
                'headers' => [
                    'User-Agent' => 'Testing 1.0'
                ]
            ]);
            try {
                if ($document->document_type_id == "01" || $document->document_type_id == "07" || $document->document_type_id == "08") {
                    $document_xml = $document->document_type_id != "01" ? "" : file_get_contents(Storage::disk('tenant')->path("signed" . DIRECTORY_SEPARATOR . $document->filename . ".xml"));
                    $document_cdr = $document->document_type_id != "01" ? "" : file_get_contents(Storage::disk('tenant')->path("cdr" . DIRECTORY_SEPARATOR . "R-" . $document->filename . ".zip"));

                    $response = $this->client->post($url . '/api/multimedia', [
                        'headers' => ['Authorization' => 'Bearer ' . $token_whatsapp],
                        'multipart' => [
                            [
                                'name'     => 'number',
                                'contents' => trim($request->customer_telephone)
                            ],
                            [
                                'name'     => 'message',
                                'contents' => $request->mensaje
                            ],
                            [
                                'name'     => 'sender',
                                'contents' => $sender_number
                            ],
                            [
                                'name'     => 'file',
                                'contents' => $document_download,
                                'filename' => $document->filename . ".pdf"
                            ],

                        ]
                    ]);

                    if (file_exists(Storage::disk('tenant')->path("signed" . DIRECTORY_SEPARATOR . $document->filename . ".xml"))) {
                        $response = $this->client->post($url . '/api/multimedia', [
                            'headers' => ['Authorization' => 'Bearer ' . $token_whatsapp],
                            'multipart' => [
                                [
                                    'name'     => 'number',
                                    'contents' => trim($request->customer_telephone)
                                ],
                                [
                                    'name'     => 'message',
                                    'contents' => "Se adjunta el XML de " . $document->filename
                                ],
                                [
                                    'name'     => 'sender',
                                    'contents' => $sender_number
                                ],
                                [
                                    'name'     => 'file',
                                    'contents' => $document_xml,
                                    'filename' => $document->filename . ".xml"
                                ],

                            ]
                        ]);
                    }

                    if (file_exists(Storage::disk('tenant')->path("cdr" . DIRECTORY_SEPARATOR . "R-" . $document->filename . ".zip"))) {
                        $response = $this->client->post($url . '/api/multimedia', [
                            'headers' => ['Authorization' => 'Bearer ' . $token_whatsapp],
                            'multipart' => [
                                [
                                    'name'     => 'number',
                                    'contents' => trim($request->customer_telephone)
                                ],
                                [
                                    'name'     => 'message',
                                    'contents' => "Se adjunta el CDR de " . $document->filename
                                ],
                                [
                                    'name'     => 'sender',
                                    'contents' => $sender_number
                                ],
                                [
                                    'name'     => 'file',
                                    'contents' => $document_cdr,
                                    'filename' => "R-" . $document->filename . ".zip"
                                ],

                            ]
                        ]);
                    }
                    $txt = $response->getBody()->getContents();
                    $data = json_decode($txt, true);
                } else {
                    $response = $this->client->post($url . '/api/multimedia', [
                        'headers' => ['Authorization' => 'Bearer ' . $token_whatsapp],
                        'multipart' => [
                            [
                                'name'     => 'number',
                                'contents' => trim($request->customer_telephone)
                            ],
                            [
                                'name'     => 'message',
                                'contents' => $request->mensaje
                            ],
                            [
                                'name'     => 'sender',
                                'contents' => $sender_number
                            ],
                            [
                                'name'     => 'file',
                                'contents' => $document_download,
                                'filename' => $document->filename . ".pdf"
                            ],

                        ]
                    ]);
                    $txt = $response->getBody()->getContents();
                    $data = json_decode($txt, true);
                }


                return response()->json(
                    [
                        'success' => true,
                        'message' => $data['success'] == false ? $data['message'] : "Se envio el mensaje con éxito",
                        'origen' => $sender_number,
                        'destinatario' => $request->numero,
                        'tipo_mensaje' => "media",

                    ]
                );
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                return $e->getResponse();
            }
        }
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

            dd($response);
            return $response;
            // $response =$this->client->post($url.'/api/status', [

            //     'multipart' => [
            //         [
            //             'name'     => 'sender',
            //             'contents' => $sender_number
            //         ],
            //      ]
            // ]);
            // dd($response);
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

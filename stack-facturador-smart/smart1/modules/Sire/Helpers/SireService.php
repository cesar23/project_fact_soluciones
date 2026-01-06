<?php

namespace Modules\Sire\Helpers;

use App\Traits\LockedEmissionTrait;
use Illuminate\Support\Collection;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\Document;
use App\Models\Tenant\Company;
use Hyn\Tenancy\Environment;
use Illuminate\Support\Facades\Log;

class SireService
{
    /*
     * URLs Services
     */
    // scope
    public static $SCOPE = 'https://api-sire.sunat.gob.pe';
    // 5.1
    public static $TOKEN = 'https://api-seguridad.sunat.gob.pe/v1/clientessol/CLIENT_ID/oauth2/token/';
    // 5.2 Sale | codLibro = 140000
    // 5.33 Purchase | codLibro  = 080000
    public static $PERIODS = 'https://api-sire.sunat.gob.pe/v1/contribuyente/migeigv/libros/rvierce/padron/web/omisos/COD_LIBRO/periodos';
    // 5.18 Sale     | type = rvie | suffix = exportapropuesta?codTipoArchivo=0
    // 5.34 Purchase | type = rce  | suffix = exportacioncomprobantepropuesta?codTipoArchivo=0&codOrigenEnvio=1
    public static $PROPOSAL = 'https://api-sire.sunat.gob.pe/v1/contribuyente/migeigv/libros/TYPE/propuesta/web/propuesta/PERIOD/SUFFIX';
    // 5.16 Sale
    // 5.31 Purchase
    public static $QUERY = 'https://api-sire.sunat.gob.pe/v1/contribuyente/migeigv/libros/rvierce/gestionprocesosmasivos/web/masivo/consultaestadotickets?perIni=PERIOD&perFin=PERIOD&page=NUM_PAGE&perPage=20&numTicket=NUM_TICKET';
    // 5.17 Sale | suffix = &codLibro=140000
    // 5.32 Purchase | suffix = null
    public static $DOWNLOAD = 'https://api-sire.sunat.gob.pe/v1/contribuyente/migeigv/libros/rvierce/gestionprocesosmasivos/web/masivo/archivoreporte?nomArchivoReporte=FILENAME&codTipoArchivoReporte=01SUFFIX';
  //public static $DOWNLOAD = "https://api-sire.sunat.gob.pe/v1/contribuyente/migeigv/libros/rvierce/gestionprocesosmasivos/web/masivo/archivoreporte?nomArchivoReporte=FILENMAE&codTipoArchivoReporte=00&codLibro=140000&perTributario="+ cbPeriodo.SelectedValue.ToString() + "&codProceso=10&numTicket=" + tk;
    // 5.8 Sale
    public static $ACCEPT = 'https://api-sire.sunat.gob.pe/v1/contribuyente/migeigv/libros/rvie/propuesta/web/propuesta/PERIOD/aceptapropuesta';

    private function getCompany()
    {
        $company = Company::first();
        return $company;
    }

    public function getToken($force = false)
    {
        // if ($force || !($token = Cache::get('sire_token'))) {
        $queryToken = $this->queryToken();
        if (!$queryToken['success']) {
            return $queryToken;
        }
        $token = $queryToken['access_token'];
        // }

        return [
            'success' => true,
            'token' => $token
        ];
    }

    private function queryToken()
    {
        $company = $this->getCompany();
        $client_id = $company->sire_client_id;
        $url_base = self::$TOKEN;
        $url = str_replace('CLIENT_ID', $client_id, $url_base);
        $body = [
            'grant_type' => 'password',
            'scope' => self::$SCOPE,
            'client_id' => $client_id,
            'client_secret' => $company->sire_client_secret,
            'username' => $company->sire_username,
            'password' => $company->sire_password,
        ];

        $client = new Client();
        $response = $client->request('POST', $url, [
            'form_params' => $body,
            'verify' => false,
        ]);

        $statusCode = $response->getStatusCode();
        $data = json_decode($response->getBody(), true);

        if ($statusCode !== 200) {
            return [
                'success' => false,
                'code' => $statusCode,
                'error' => $data
            ];
        }

        if ($statusCode === 200) {
            $this->updateToken($data['access_token'], $data['expires_in']);
            return [
                'success' => true,
                'code' => $statusCode,
                'access_token' => $data['access_token']
            ];
        }
    }

    private function updateToken($token, $expire)
    {
        Cache::put('sire_token', $token, now()->addSeconds($expire));
    }
    function checkSire()
    {
        $company = $this->getCompany();
        if (
            !$company->sire_client_id ||
            !$company->sire_client_secret ||
            !$company->sire_username ||
            !$company->sire_password
        ) {
            return [
                'success' => false,
                'code' => 500,
                'message' => 'No se ha configurado el servicio SIRE'
            ];
        }
        return null;
    }
    public function getPeriods($type)
    {
        $fail = $this->checkSire();
        if ($fail) {
            return $fail;
        }

        switch ($type) {
            case 'sale':
                $cod_libro = '140000';
                break;
            case 'purchase':
                $cod_libro = '080000';
                break;
        }
        $get_token = $this->getToken();
        $token = $get_token['token'];

        $url_base = self::$PERIODS;
        $url = str_replace('COD_LIBRO', $cod_libro, $url_base);
        $client = new Client();
        $response = $client->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
            'verify' => false,
        ]);

        $statusCode = $response->getStatusCode();
        $data = json_decode($response->getBody(), true);

        if ($statusCode !== 200) {
            return [
                'success' => false,
                'code' => $statusCode,
                'error' => $data
            ];
        }

        if ($statusCode === 200) {
            return [
                'success' => true,
                'code' => $statusCode,
                'data' => $data
            ];
        }
    }

    public function getTicket($type, $period)
    {
        $fail =  $this->checkSire();
        if ($fail) {
            return $fail;
        }
        $get_token = $this->getToken();
        $token = $get_token['token'];

        switch ($type) {
            case 'sale':
                $suffix = 'exportapropuesta?codTipoArchivo=0';
                $url = str_replace(['TYPE', 'PERIOD', 'SUFFIX'], ['rvie', $period, $suffix], self::$PROPOSAL);
                break;
            case 'purchase':
                $suffix = 'exportacioncomprobantepropuesta?codTipoArchivo=0&codOrigenEnvio=1';
                $url = str_replace(['TYPE', 'PERIOD', 'SUFFIX'], ['rce', $period, $suffix], self::$PROPOSAL);
                break;
        }

        $client = new Client();
        $response = $client->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
            'verify' => false,
        ]);

        $statusCode = $response->getStatusCode();
        $data = json_decode($response->getBody(), true);

        if ($statusCode !== 200) {
            return [
                'success' => false,
                'code' => $statusCode,
                'error' => $data
            ];
        }

        if ($statusCode === 200) {
            return [
                'success' => true,
                'code' => $statusCode,
                'data' => $data
            ];
        }
    }

    public function queryTicket($page, $period, $ticket, $type)
    {
        $fail =  $this->checkSire();
        if ($fail) {
            return $fail;
        }
        $get_token = $this->getToken();
        $token = $get_token['token'];

        $url = str_replace(['NUM_PAGE', 'PERIOD', 'NUM_TICKET'], [$page, $period, $ticket], self::$QUERY);

        $client = new Client();
        $response = $client->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
            'verify' => false,
        ]);

        $statusCode = $response->getStatusCode();
        $data = json_decode($response->getBody(), true);
        if ($statusCode !== 200) {
            return [
                'success' => false,
                'code' => $statusCode,
                'error' => $data
            ];
        }

        if ($statusCode === 200) {
            $records = $this->validateAndGetValue($data, 'registros');
            if (!empty($records)) {
                $record = $records[0];
                $status_code = $record['codEstadoProceso'];
                $filename = isset($record['archivoReporte']) ? $record['archivoReporte'][0]['nomArchivoReporte'] : null;
                $documents = null;
                if ($status_code == '06' && $filename != null) {
                    $documents = $this->queryFile($filename, $type,$period,$ticket);
                }
                return [
                    'success' => true,
                    'code' => $statusCode,
                    'data' => [
                        'status_code' => $status_code,
                        'filename' => $filename,
                        'documents' => $documents,
                    ],
                ];
            } else {
                return [
                    'success' => false,
                    'code' => $statusCode,
                    'message' => 'SIRE. No hay registros en esta página'
                ];
            }
        }
    }

    function validateAndGetValue($object, $key)
    {
        return isset($object[$key]) ? $object[$key] : null;
    }

    public function queryFile($filename, $type,$period,$ticket)
    {
        $get_token = $this->getToken();
        $token = $get_token['token'];

        switch ($type) {
            case 'sale':
                // $suffix = '&codLibro=140000';
                $suffix = '&codProceso=10&perTributario='.$period."&numTicket=".$ticket;
                $url = str_replace(['FILENAME', 'SUFFIX'], [$filename, $suffix], self::$DOWNLOAD);
                break;
            case 'purchase':
                $suffix = '&codProceso=10&perTributario='.$period."&numTicket=".$ticket;
                $url = str_replace(['FILENAME', 'SUFFIX'], [$filename, $suffix], self::$DOWNLOAD);
                break;
        }

        try {
            $client = new Client();
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
                'verify' => false,
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                return [
                    'success' => false,
                    'code' => $statusCode,
                    'error' => $response->getBody()
                ];
            }

            if ($statusCode === 200) {
                $file = $response->getBody()->getContents();

                $temp = sys_get_temp_dir() . '/' . $filename; // Almacena el archivo ZIP en una ubicación temporal
                file_put_contents($temp, $file);

                $zip = new \ZipArchive;
                if ($zip->open($temp) === true) {
                    $numFiles = $zip->numFiles;
                    $filename_txt = $zip->getNameIndex(0);

                    $extractPath = storage_path('app' . DIRECTORY_SEPARATOR . 'tenancy' . DIRECTORY_SEPARATOR . 'tenants' . DIRECTORY_SEPARATOR . app(Environment::class)->tenant()->uuid . DIRECTORY_SEPARATOR . 'txt');
                    if (!Storage::exists($extractPath)) {
                        Storage::makeDirectory($extractPath);
                    }
                    $zip->extractTo($extractPath);
                    $zip->close();

                    $path_txt = $extractPath . DIRECTORY_SEPARATOR . $filename_txt;
                    if (Storage::disk('tenant')->exists('txt' . DIRECTORY_SEPARATOR . $filename_txt)) {
                        $lines = file($path_txt, FILE_IGNORE_NEW_LINES); // Storage no funcionó
                        array_shift($lines); // Eliminando cabeceras
                        $dataCollection = new Collection();
                        if ($type == 'sale') {
                            foreach ($lines as $line) {
                                $values = explode('|', $line);
                                $serie = $values[7];
                                $number = (int) $values[8];
                                $number_company = $values[11];
                                $name_company = $values[12];
                                $proSmartRow = [
                                    'service' => 'Sunat',
                                    'date' => $values[4], // 2023/05/01
                                    'document_type' => $values[6],
                                    'serie' => $serie,
                                    'number' => $number,
                                    'number_company' => $number_company,
                                    'name_company' => $name_company,
                                    'total' => number_format($values[25], 2)
                                ];
                                $document = Document::where('series', $serie)
                                    ->where('number', $number)
                                    ->where('document_type_id', $values[6])
                                    ->first();
                                $diff = false;
                                if ($document) {
                                    $total = $document->total;
                                    $currency_type_id = $document->currency_type_id;
                                    if ($currency_type_id != "PEN") {
                                        $total = $document->total * $document->exchange_rate_sale;
                                    }
                                    $number_company = $document->customer->number;
                                    $name_company = $document->customer->name;
                                    $document_total = $document->state_type_id == '05' ? $document->total : 0;
                                    $document_total = $document->document_type_id == '07' ? $document_total * -1 : $document_total;
                                    $total = (string) $document_total;
                                    $total_prosmart = (string) $values[25];

                                    if ($total != $total_prosmart) {
                                        $diff = true;
                                    }
                                    $dataCollection->push([
                                        'number_company' => $number_company,
                                        'name_company' => $name_company,
                                        'label' => $diff ? 'DANGER' : null,
                                        'service' => 'Smart',
                                        'date' => $document->date_of_issue->format('Y/m/d'),
                                        'document_type' => $document->document_type_id,
                                        'serie' => $document->series,
                                        'number' => $document->number,
                                        'total' => number_format($document_total, 2),
                                    ]);
                                } else {
                                    $proSmartRow['label'] = 'DANGER';
                                }
                                if ($diff) {
                                    $proSmartRow['label'] = 'STRONG';
                                }
                                $dataCollection->push($proSmartRow);
                            }
                        } else {
                            foreach ($lines as $line) {
                                $values = explode('|', $line);
                                $serie = $values[7];
                                $number = (int)$values[9];
                                $number_company = $values[12];
                                $name_company = $values[13];
                                $diff = false;
                                $proSmartRow = [
                                    'number_company' => $number_company,
                                    'name_company' => $name_company,
                                    'service' => 'Sunat',
                                    'date' => $values[4], //2023/05/01
                                    'document_type' => $values[6],
                                    'serie' => $serie,
                                    'number' => $number,
                                    'total' => number_format($values[24], 2)
                                ];


                                $purchase = Purchase::where('series', $serie)
                                    ->where('number', $number)
                                    ->first();
                                if ($purchase) {
                                    $total = $purchase->total;
                                    $currency_type_id = $purchase->currency_type_id;
                                    if ($currency_type_id != "PEN") {
                                        $total = $purchase->total * $purchase->exchange_rate_sale;
                                    }
                                    $total = (string) number_format($total, 2);
                                    $total_prosmart = (string) number_format($values[24], 2);

                                    if ($total != $total_prosmart) {
                                        $diff = true;
                                    }
                                    $number_company = $purchase->supplier->number;
                                    $name_company = $purchase->supplier->name;
                                    $dataCollection->push([
                                        'number_company' => $number_company,
                                        'name_company' => $name_company,
                                        'label' => $diff ? 'DANGER' : null,
                                        'service' => 'Smart',
                                        'date' => $purchase->date_of_issue->format('d/m/Y'),
                                        'document_type' => $purchase->document_type_id,
                                        'serie' => $purchase->series,
                                        'number' => $purchase->number,
                                        'total' => $total,
                                    ]);
                                } else {
                                    $proSmartRow['label'] = 'DANGER';
                                }
                                if ($diff) {
                                    $proSmartRow['label'] = 'STRONG';
                                }
                                $dataCollection->push($proSmartRow);
                            }
                        }
                        return $dataCollection;
                    } else {
                        // Error: el archivo no existe o se guardó incorrectamente
                        return [
                            'success' => false,
                            'code' => 500,
                            'message' => 'El archivo no existe o se guardó incorrectamente'
                        ];
                    }

                    unlink($temp);
                } else {
                    // Manejo de errores al abrir el archivo ZIP
                    return [
                        'success' => false,
                        'code' => 500,
                        'message' => 'Error al abrir el archivo ZIP'
                    ];
                }
            }
        } 
        catch (\GuzzleHttp\Exception\ClientException $e) {
            // Captura errores del cliente
            $responseBody = $e->getResponse()->getBody()->getContents();
            return [
                'success' => false,
                'code' => 500,
                'message' => $responseBody
            ];
        }
        
        catch (\Exception $e) {
            return [
                'success' => false,
                'code' => 500,
                'message' => 'Error al descargar el archivo'
            ];
        }
    }

    public function sendAccept($period)
    {
        $get_token = $this->getToken();
        $token = $get_token['token'];

        $url = str_replace('PERIOD', $period, self::$ACCEPT);

        $client = new Client();
        $response = $client->request('POST', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
            'verify' => false,
        ]);

        $statusCode = $response->getStatusCode();
        $data = json_decode($response->getBody(), true);

        if ($statusCode !== 200) {
            return [
                'success' => false,
                'code' => $statusCode,
                'error' => $data
            ];
        }

        if ($statusCode === 200) {
            return [
                'success' => true,
                'code' => $statusCode,
                'data' => $data
            ];
        }
    }
}

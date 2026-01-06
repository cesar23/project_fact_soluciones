<?php

namespace App\Http\Controllers\System;

use App\CoreFacturalo\Helpers\Certificate\GenerateCertificate;
use App\Http\Controllers\Controller;
use App\Http\Requests\System\ClientRequest;
use App\Http\Resources\System\ClientCollection;
use App\Http\Resources\System\ClientDocumentCollection;
use App\Http\Resources\System\ClientResource;
use App\Models\System\Client;
use App\Models\System\Configuration;
use App\Models\System\Module;
use App\Models\System\Plan;
use Carbon\Carbon;
use Exception;
use Hyn\Tenancy\Contracts\Repositories\HostnameRepository;
use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Document\Helpers\DocumentHelper;
use Modules\MobileApp\Models\System\AppModule;
use App\CoreFacturalo\ClientHelper;
use App\CoreFacturalo\Services\IntegratedQuery\AuthApi;
use App\CoreFacturalo\Services\IntegratedQuery\ValidateCpe;
use App\Http\Controllers\Tenant\ConfigurationController;
use App\Http\Controllers\Tenant\DocumentController;
use App\Http\Controllers\Tenant\VoidedController;
use App\Http\Resources\System\ClientSimpleCollection;
use App\Http\Resources\System\PlanCollection;
use App\Mail\MessageMail;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration as TenantConfiguration;
use App\Models\Tenant\Document;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Person;
use App\Models\Tenant\StateType;
use App\Traits\CacheTrait;
use Illuminate\Support\Facades\Mail;
use Ifsnop\Mysqldump as IMysqldump;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    use CacheTrait;
    protected $soap_url;
    protected $soap_password;
    //constructor
    public function __construct()
    {
        // $this->soap_url = "https://prod.conose.cpe.pe/ol-ti-itcpe/billService?wsdl";
        $this->soap_url = "https://ose.cpe.pe/ol-ti-itcpe/billService?wsdl";
        $this->soap_password = "oJ0Aa8AK";
    }
    /**
     * Descarga los archivos de los clientes seleccionados en un archivo ZIP
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function downloadDocuments(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            // 1. Preparar datos iniciales
            $selectedClientIds = $request->input('selectedClientIds', []);
            $date_start = $request->input('date_start') ? Carbon::parse($request->input('date_start'))->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
            $date_end = $request->input('date_end') ? Carbon::parse($request->input('date_end'))->endOfDay() : Carbon::now()->endOfDay();

            $clients = empty($selectedClientIds) ? Client::query() : Client::whereIn('id', $selectedClientIds);

            // 2. Preparar directorios
            $downloadPath = storage_path('app/downloads');
            $tempPath = $downloadPath . '/temp_' . time();

            // Crear directorios si no existen
            foreach ([$downloadPath, $tempPath] as $path) {
                if (!is_dir($path)) {
                    if (!mkdir($path, 0755, true)) {
                        return [
                            'success' => false,
                            'message' => 'No se pudo crear el directorio: ' . $path
                        ];
                    }
                }
            }

            // 3. Nombre del archivo ZIP con timestamp único
            $timestamp = date('Y-m-d_H-i-s');
            $zipFileName = "client_files_{$timestamp}.zip";
            $zipFilePath = $downloadPath . '/' . $zipFileName;

            // Eliminar archivo si ya existe
            if (file_exists($zipFilePath)) {
                @unlink($zipFilePath);
            }

            // 4. Preparar datos de los clientes a procesar
            $clientsToProcess = [];
            $processedClients = 0;

            $clients->chunk(100, function ($clientsChunk) use (&$processedClients, &$clientsToProcess) {
                foreach ($clientsChunk as $client) {
                    try {
                        // Verificar si el cliente tiene hostname y website
                        if (!$client->hostname || !$client->hostname->website) {
                            continue;
                        }

                        $clientsToProcess[] = [
                            'client_id' => $client->id,
                            'tenant' => $client->hostname->website,
                            'name' => $client->name,
                            'number' => $client->number
                        ];

                        $processedClients++;
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Error preparando cliente {$client->id}: " . $e->getMessage());
                        continue;
                    }
                }
            });

            if ($processedClients === 0) {
                $this->cleanupDirectory($tempPath);
                return [
                    'success' => false,
                    'message' => 'No se encontraron clientes para procesar'
                ];
            }

            // 5. Crear archivo ZIP
            $zip = new \ZipArchive();
            if ($zip->open($zipFilePath, \ZipArchive::CREATE) !== true) {
                $this->cleanupDirectory($tempPath);
                return [
                    'success' => false,
                    'message' => 'No se pudo crear el archivo ZIP'
                ];
            }

            // 6. Procesar archivos de cada cliente
            foreach ($clientsToProcess as $clientData) {
                try {
                    // Configurar la conexión tenant
                    $tenancy = app(Environment::class);
                    $tenancy->tenant($clientData['tenant']);

                    // Obtener los archivos del tenant
                    $files = Storage::disk('tenant')->allFiles();
                    $clientFolder = "{$clientData['number']}_{$clientData['name']}";

                    foreach ($files as $file) {
                        try {
                            // Obtener fecha de modificación del archivo
                            $file_time = Storage::disk('tenant')->lastModified($file);
                            $file_date = Carbon::createFromTimestamp($file_time);

                            // Verificar si el archivo está dentro del rango de fechas
                            if ($file_date->between($date_start, $date_end)) {
                                // Leer el contenido del archivo
                                $contents = Storage::disk('tenant')->get($file);

                                // Añadir archivo al ZIP
                                $zip->addFromString("{$clientFolder}/{$file}", $contents);
                            }
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error("Error procesando archivo {$file} del cliente {$clientData['number']}: " . $e->getMessage());
                            continue;
                        }
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Error procesando archivos del cliente {$clientData['number']}: " . $e->getMessage());
                    continue;
                }
            }

            // 7. Cerrar el ZIP
            if (!$zip->close()) {
                $this->cleanupDirectory($tempPath);
                return [
                    'success' => false,
                    'message' => 'Error al cerrar el archivo ZIP'
                ];
            }

            // 8. Verificar que el ZIP se creó correctamente y tiene contenido
            if (!file_exists($zipFilePath) || filesize($zipFilePath) === 0) {
                return [
                    'success' => false,
                    'message' => 'El archivo ZIP no se creó correctamente o está vacío'
                ];
            }

            // 9. Limpiar archivos temporales
            $this->cleanupDirectory($tempPath);

            // 10. Devolver el archivo ZIP
            return response()->download($zipFilePath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error en downloadClientFiles: " . $e->getMessage());

            // Limpiar directorios temporales si existen
            if (isset($tempPath) && is_dir($tempPath)) {
                $this->cleanupDirectory($tempPath);
            }

            return [
                'success' => false,
                'message' => 'Error en el proceso: ' . $e->getMessage()
            ];
        }
    }

    public function documents()
    {
        return view('system.clients.documents');
    }
    public function status_voided($id, $voided_id)
    {
        $client = Client::findOrFail($id);
        $tenancy = app(Environment::class);
        $tenancy->tenant($client->hostname->website);


        try {
            $response = (new VoidedController)->status($voided_id);
            if ($response["success"]) {

                $message = $response['message'];
                return [
                    "success" => true,
                    "message" => $message,
                ];
            }
            return [
                "success" => false,
                "message" => $response['message'],
            ];
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function send($id, $document_id)
    {
        $client = Client::findOrFail($id);
        $tenancy = app(Environment::class);
        $tenancy->tenant($client->hostname->website);
        $documents = DB::connection('tenant')->table('documents')
            ->where('id', $document_id)
            ->first();

        try {
            $response = (new DocumentController)->send($documents->id);
            $code = isset($response['code']) ? $response['code'] : null;
            if ($response["success"]) {
                $code = "0";
            }
            DB::connection('tenant')->table('documents')->where('id', $documents->id)
                ->update([
                    'sunat_shipping_status' => json_encode($response),
                    'success_sunat_shipping_status' => $code == "0" ? true : false
                ]);
            $message = $response['message'];
            return [
                "success" => true,
                "message" => $message,
            ];
        } catch (\Exception $e) {
            DB::connection('tenant')->table('documents')->where('id', $id)
                ->update([
                    'sunat_shipping_status' => json_encode([
                        'sucess' => false,
                        'message' => $e->getMessage(),
                        'payload' => $e
                    ]),
                    'success_sunat_shipping_status' => false
                ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function tables_clients()
    {
        $taxpayers = Client::get()->transform(function ($row) {
            return [
                "id" => $row->id,
                "number" => $row->number,
                "name"  => $row->name,
                "email" => $row->email,
                "token" => $row->token,
                "hostname" => $row->hostname->fqdn,
            ];
        });

        $document_type = DB::table('cat_document_types')->where('active', '1')->get();
        $state_type = DB::table('state_types')->get();
        return compact('document_type', 'state_type', 'taxpayers');
    }
    public function records_clients(Request $request)
    {
        // Preparar filtros y fechas
        $filters = $this->prepareClientFilters($request);
        $data_documents = new Collection();

        // Procesar según el tipo de consulta
        if ($request->id == "0") {
            // Procesar todos los clientes
            $this->processAllClientsDocuments($filters, $data_documents);
        } else {
            // Procesar un cliente específico
            $this->processSingleClientDocuments($request->id, $filters, $data_documents);
        }
        // Paginar usando ClientDocumentCollection
        return new ClientDocumentCollection($data_documents->paginate(50));
    }

    /**
     * Preparar filtros y fechas para la consulta
     */
    private function prepareClientFilters(Request $request): array
    {
        $period = $request->period_id;
        $d_start = null;
        $d_end = null;

        switch ($period) {
            case 'month':
                $d_start = Carbon::parse($request->month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($request->month_start . '-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'between_months':
                $d_start = Carbon::parse($request->month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($request->month_end . '-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'date':
                $d_start = $request->date_start;
                $d_end = $request->date_start;
                break;
            case 'between_dates':
                $d_start = $request->date_start;
                $d_end = $request->date_end;
                break;
        }

        return [
            'state_type_id' => $request->state_type_id,
            'document_type_id' => $request->document_type_id,
            'date_start' => $d_start,
            'date_end' => $d_end,
        ];
    }

    /**
     * Procesar documentos de todos los clientes
     */
    private function processAllClientsDocuments(array $filters, Collection $data_documents): void
    {
        $records = Client::with('hostname.website')->latest()->get();

        foreach ($records as $client) {
            $this->processClientDocuments($client, $filters, $data_documents, true);
        }
    }

    /**
     * Procesar documentos de un cliente específico
     */
    private function processSingleClientDocuments(int $clientId, array $filters, Collection $data_documents): void
    {
        $client = Client::with('hostname.website')->findOrFail($clientId);
        $this->processClientDocuments($client, $filters, $data_documents, false);
    }

    /**
     * Procesar documentos de un cliente en su tenant
     */
    private function processClientDocuments(Client $client, array $filters, Collection $data_documents, bool $includeProtocol): void
    {
        $tenancy = app(Environment::class);
        $tenancy->tenant($client->hostname->website);

        // Obtener company
        $company = DB::connection('tenant')->table('companies')->first();

        // Obtener state_types cacheados para evitar consultas repetitivas
        $stateTypes = Cache::remember('state_types_desc', 3600, function () {
            return StateType::pluck('description', 'id')->toArray();
        });

        // Query optimizada con JOINs para evitar N+1
        $query = DB::connection('tenant')
            ->table('documents as d')
            ->join('cat_document_types as dt', 'd.document_type_id', '=', 'dt.id')
            ->leftJoin('voided_documents as vd', 'vd.document_id', '=', 'd.id')
            ->select(
                'd.id',
                'd.external_id',
                'd.document_type_id',
                'd.series',
                'd.number',
                'd.date_of_issue',
                'd.customer',
                'd.total',
                'd.total_taxed',
                'd.total_igv',
                'd.state_type_id',
                'vd.voided_id',
                'dt.description as document_type'
            );

        // Aplicar filtros
        if ($filters['document_type_id'] != "0") {
            $query->where('d.document_type_id', $filters['document_type_id']);
        }
        if ($filters['state_type_id'] != "0") {
            $query->where('d.state_type_id', $filters['state_type_id']);
        }
        if ($filters['date_start'] && $filters['date_end']) {
            $query->whereBetween('d.date_of_issue', [$filters['date_start'], $filters['date_end']]);
        }

        // Preparar hostname con protocolo
        $protocol = $includeProtocol ? (strpos(url()->current(), 'https') !== false ? 'https://' : 'http://') : '';
        $hostname = $protocol . $client->hostname->fqdn;

        // Procesar documentos
        $documents = $query->get();

        foreach ($documents as $doc) {
            $customer = json_decode($doc->customer);

            $data_documents->push((object)[
                'client_id' => $client->id,
                'hostname' => $hostname,
                'token' => $client->token,
                'company_name' => $company->name,
                'company_number' => $company->number,
                'document_type_id' => $doc->document_type_id,
                'external_id' => $doc->external_id,
                'document_id' => $doc->id,
                'document_type' => $doc->document_type,
                'series' => $doc->series,
                'number' => $doc->number,
                'date_of_issue' => $doc->date_of_issue,
                'customer_name' => $customer->name ?? '',
                'customer_number' => $customer->number ?? '',
                'total' => $doc->total,
                'total_taxed' => $doc->total_taxed,
                'total_igv' => $doc->total_igv,
                'state_type' => $stateTypes[$doc->state_type_id] ?? '',
                'state_type_id' => $doc->state_type_id,
                'voided_id' => $doc->voided_id,
            ]);
        }
    }
    public function validate_documents($hostname_id, $documents_id)
    {

        $row = Client::findOrFail($hostname_id);
        $tenancy = app(Environment::class);
        $tenancy->tenant($row->hostname->website);

        $document = DB::connection('tenant')
            ->table('documents')->where('id', $documents_id)
            ->orderBy('series')
            ->orderBy('number')
            ->first();
        $company = DB::connection('tenant')
            ->table('companies')
            ->first();
        $auth_api = $this->getCompanyToken($company);

        $access_token = $auth_api['data']['access_token'];
        $state_types = DB::table('state_types')->get();
        if (!$auth_api['success']) {
            $this->info($auth_api['message']);
        } else {

            $access_token = $auth_api['data']['access_token'];
            $state_types = DB::table('state_types')->get();
            $count = 0;
            $validate_cpe = new ValidateCpe(
                $access_token,
                $company->number,
                $document->document_type_id,
                $document->series,
                $document->number,
                $document->date_of_issue,
                $document->total
            );
            $response = $validate_cpe->search();

            if ($response['success']) {
                $response_description = $response['message'];
                $response_code = $response['data']['estadoCp'];
                $response_state_type_id = $response['data']['state_type_id'];

                $state_type = $state_types->first(function ($state) use ($response_state_type_id) {
                    return $state->id === $response_state_type_id;
                });

                $state_type_description = $state_type ? $state_type->description : 'No existe';

                // $message = $count.': '.$document->number.' | Código: '.$response_code.' | Mensaje: '.$response_description
                //             .'| Estado Sistema: '.$document->state_type_id
                //             .' | Estado Sunat: '.$response_state_type_id.' - '.$state_type_description;
            }
        }
    }
    public function documents_to_anulated($id)
    {
        $client = Client::findOrFail($id);
        $tenancy = app(Environment::class);
        $tenancy->tenant($client->hostname->website);
        $data = DB::connection('tenant')
            ->table('documents')
            ->whereIn('state_type_id', ['09', '13'])
            ->get();

        $count = 0;
        $auth_api = (new AuthApi())->getToken();
        $access_token = $auth_api['data']['access_token'];
        $company = Company::first();
        foreach ($data as $document) {
            $count++;
            $validate_cpe = new ValidateCpe(
                $access_token,
                $company->number,
                $document->document_type_id,
                $document->series,
                $document->number,
                $document->date_of_issue,
                $document->total
            );

            $response = $validate_cpe->search();

            if ($response['success']) {
                $response_state_type_id = $response['data']['state_type_id'] == "-1" ? "01" : $response['data']['state_type_id'];
                DB::connection('tenant')
                    ->table('documents')
                    ->where('id',  $document->id)->update([
                        "state_type_id" => $response_state_type_id
                    ]);
            }
        }
        return response()->json([
            'success' => true,
            'message' => "Se valido N°" . $count . " comprobantes  con éxito"
        ]);
    }
    public function documents_not_send($id)
    {
        $client = Client::findOrFail($id);
        $tenancy = app(Environment::class);
        $tenancy->tenant($client->hostname->website);
        $data = Document::query()
            ->where('group_id', '01')
            ->where('send_server', 0)
            ->whereIn('state_type_id', ['01', '03'])
            ->where('success_sunat_shipping_status', false)
            ->get();
        $count = 0;
        foreach ($data as $document) {
            try {
                $response = (new DocumentController)->sendPse($document->id);
                $document->sunat_shipping_status = json_encode($response);
                $document->success_sunat_shipping_status = true;
                $document->save();
                $count++;
            } catch (\Exception $e) {
                $document->success_sunat_shipping_status = false;
                $document->sunat_shipping_status = json_encode([
                    'sucess' => false,
                    'message' => $e->getMessage(),
                    'payload' => $e
                ]);

                $document->save();
            }
        }
        return response()->json([
            'success' => true,
            'data' => "Se envio " . $count . " comprobantes  con éxito"
        ]);
    }

    public function downloadDatabase(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        try {
            // 1. Preparar datos iniciales
            $selectedClientIds = $request->input('selectedClientIds', []);
            $clients = empty($selectedClientIds) ? Client::query() : Client::whereIn('id', $selectedClientIds);

            // 2. Preparar directorios
            $backupPath = storage_path('app/backups');
            $tempPath = $backupPath . '/temp_' . time();

            // Crear directorios si no existen
            foreach ([$backupPath, $tempPath] as $path) {
                if (!is_dir($path)) {
                    if (!mkdir($path, 0755, true)) {
                        return [
                            'success' => false,
                            'message' => 'No se pudo crear el directorio: ' . $path
                        ];
                    }
                }
            }

            // 3. Nombre del archivo ZIP con timestamp único
            $timestamp = date('Y-m-d_H-i-s');
            $zipFileName = "all_databases_{$timestamp}.zip";
            $zipFilePath = $backupPath . '/' . $zipFileName;

            // 4. Procesar clientes y crear archivos SQL
            $processedClients = 0;
            $clientsToProcess = [];

            $clients->chunk(100, function ($clientsChunk) use (&$processedClients, &$clientsToProcess, $tempPath) {
                foreach ($clientsChunk as $client) {
                    try {
                        // Verificar si el cliente tiene hostname y website
                        if (!$client->hostname || !$client->hostname->website) {
                            continue;
                        }

                        $tenant = $client->hostname->website;
                        $database = $tenant->uuid;

                        // Guardar información para procesar después
                        $clientsToProcess[] = [
                            'client_id' => $client->id,
                            'tenant' => $tenant,
                            'database' => $database,
                            'sqlFilePath' => $tempPath . '/' . $database . '.sql'
                        ];

                        $processedClients++;
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Error preparando cliente {$client->id}: " . $e->getMessage());
                        continue;
                    }
                }
            });

            if ($processedClients === 0) {
                $this->cleanupDirectory($tempPath);
                return [
                    'success' => false,
                    'message' => 'No se encontraron clientes para procesar'
                ];
            }

            // 5. Procesar cada cliente y crear backup SQL
            foreach ($clientsToProcess as $clientData) {
                try {
                    // Configurar la conexión tenant
                    $tenancy = app(Environment::class);
                    $tenancy->tenant($clientData['tenant']);

                    // Obtener información de conexión
                    $dbConfig = config('database.connections.' . config('tenancy.db.system-connection-name', 'system'));
                    $host = $dbConfig['host'];
                    $username = $dbConfig['username'];
                    $password = $dbConfig['password'];

                    // Crear backup de base de datos
                    $tenant_dump = new IMysqldump\Mysqldump(
                        'mysql:host=' . $host . ';dbname=' . $clientData['database'],
                        $username,
                        $password
                    );

                    $tenant_dump->start($clientData['sqlFilePath']);

                    // Verificar que se creó el archivo SQL
                    if (!file_exists($clientData['sqlFilePath'])) {
                        throw new \Exception('No se pudo crear el archivo SQL');
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Error creando SQL para cliente {$clientData['client_id']}: " . $e->getMessage());
                    continue;
                }
            }

            // 6. Crear archivo ZIP usando la función nativa de PHP
            $zip = new \ZipArchive();

            // Eliminar archivo si ya existe
            if (file_exists($zipFilePath)) {
                @unlink($zipFilePath);
            }

            // Crear nuevo archivo ZIP
            if ($zip->open($zipFilePath, \ZipArchive::CREATE) !== true) {
                $this->cleanupDirectory($tempPath);
                return [
                    'success' => false,
                    'message' => 'No se pudo crear el archivo ZIP'
                ];
            }

            // 7. Añadir archivos SQL al ZIP
            foreach ($clientsToProcess as $clientData) {
                if (file_exists($clientData['sqlFilePath'])) {
                    $relativePath = $clientData['database'] . '/' . $clientData['database'] . '.sql';
                    $zip->addFile($clientData['sqlFilePath'], $relativePath);

                    // Añadir certificado si existe
                    try {
                        $tenancy = app(Environment::class);
                        $tenancy->tenant($clientData['tenant']);

                        $company = Company::first();
                        if ($company && $company->certificate) {
                            $certPath = storage_path('app/certificates/' . $company->certificate);
                            if (file_exists($certPath)) {
                                $zip->addFile(
                                    $certPath,
                                    $clientData['database'] . '/' . $company->certificate
                                );
                            }
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Error añadiendo certificado: " . $e->getMessage());
                        continue;
                    }
                }
            }

            // 8. Cerrar el ZIP
            if (!$zip->close()) {
                $this->cleanupDirectory($tempPath);
                return [
                    'success' => false,
                    'message' => 'Error al cerrar el archivo ZIP'
                ];
            }

            // 9. Verificar que el ZIP se creó correctamente y tiene contenido
            if (!file_exists($zipFilePath) || filesize($zipFilePath) === 0) {
                $this->cleanupDirectory($tempPath);
                return [
                    'success' => false,
                    'message' => 'El archivo ZIP no se creó correctamente o está vacío'
                ];
            }

            // 10. Limpiar archivos temporales
            $this->cleanupDirectory($tempPath);

            // 11. Devolver el archivo ZIP
            return response()->download($zipFilePath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error en downloadDatabase: " . $e->getMessage());

            // Limpiar directorios temporales si existen
            if (isset($tempPath) && is_dir($tempPath)) {
                $this->cleanupDirectory($tempPath);
            }

            return [
                'success' => false,
                'message' => 'Error en el proceso: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Limpia un directorio eliminando todos sus archivos y el directorio mismo
     */
    private function cleanupDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->cleanupDirectory($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($dir);
    }

    public function email(Request $request)
    {
        //get 3 vars from request: to, body, subject
        $form = $request->input('form');
        //form is a string encoded in json
        $form = json_decode($form, true);
        $to = $form['to'];
        $body = $form['body'];
        $subject = $form['subject'];
        $files = $request->file('files');
        //send mail
        $mail = new MessageMail($body, $subject, $files);
        Mail::to($to)
            ->send($mail);

        return [
            'success' => true,
            'message' => 'Email enviado correctamente',
        ];
    }
    public function index()
    {
        return view('system.clients.index');
    }
    public function columns()
    {
        return [
            'name' => 'Nombre/Documento',
            'hostname' => 'Hostname',

        ];
    }
    public function create()
    {
        return view('system.clients.form');
    }

    public function setComment(Request $request)
    {
        $client_id = $request->input('id');
        $comment = $request->input('comment');
        $client = Client::findOrFail($client_id);
        $client->comment = $comment;
        $client->save();
        return [
            'success' => true,
            'message' => 'Comentario actualizado correctamente',
        ];
    }
    public function setTelephone(Request $request)
    {
        $client_id = $request->input('id');
        $telephone = $request->input('telephone');
        $client = Client::findOrFail($client_id);
        $client->telephone = $telephone;
        $client->save();
        return [
            'success' => true,
            'message' => 'Teléfono actualizado correctamente',
        ];
    }
    public function setMonto(Request $request)
    {
        $client_id = $request->input('id');
        $monto = $request->input('monto'); // Cambié $telephone a $monto
        $client = Client::findOrFail($client_id);
        $client->monto = $monto; // Usando la variable correcta
        $client->save();
        return [
            'success' => true,
            'message' => 'Monto actualizado correctamente',
        ];
    }

    public function setTiempo(Request $request)
    {
        // Validar la entrada del formulario
        $request->validate([
            'id' => 'required|integer',
            'tiempo' => 'required|string|max:255', // Asegura que 'tiempo' sea una cadena de texto con un máximo de 255 caracteres
        ]);

        // Obtener los datos validados
        $client_id = $request->input('id');
        $tiempo = $request->input('tiempo');

        // Buscar el cliente y actualizar el campo 'tiempo'
        $client = Client::findOrFail($client_id);
        $client->tiempo = $tiempo;
        $client->save();

        // Responder con un mensaje de éxito
        return [
            'success' => true,
            'message' => 'Tiempo actualizado correctamente',
        ];
    }


    public function delete_cert_file($type, $client_id)
    {
        $client = Client::findOrFail($client_id);
        $path = 'app/public/uploads/certf';
        if ($type == 'pem') {
            $name = $client->cert_pem;
            $client->cert_pem = null;
        } else {
            $name = $client->cert_pfx;
            $client->cert_pfx = null;
        }
        $client->save();
        unlink(storage_path($path . '/' . $name));
        return [
            'success' => true,
            'message' => 'Archivo eliminado correctamente',
            'name' => $name
        ];
    }

    public function store_cert_file($type, $client_id, Request $request)
    {
        $client = Client::findOrFail($client_id);

        $file = $request->file('file');
        //create original name with extension 
        $name = $file->getClientOriginalName();

        $path = 'app/public/uploads/certf';
        $file->move(storage_path($path), $name);

        if ($type == 'pem') {
            $client->cert_pem = $name;
        } else {
            $client->cert_pfx = $name;
        }
        $client->save();
        return [
            'success' => true,
            'message' => 'Archivo subido correctamente',
            'name' => $name
        ];
    }

    public function tables()
    {

        $url_base = '.' . config('tenant.app_url_base');
        $plans = new PlanCollection(Plan::all());
        $types = [['type' => 'admin', 'description' => 'Administrador'], ['type' => 'integrator', 'description' => 'Listar Documentos']];
        $modules = Module::with('levels')
            ->where('sort', '<', 14)
            ->orderBy('sort')
            ->get()
            ->each(function ($module) {
                return $this->prepareModules($module);
            });

        $apps = Module::with('levels')
            ->where('sort', '>', 13)
            ->orderBy('sort')
            ->get()
            ->each(function ($module) {
                return $this->prepareModules($module);
            });
        $group_all = Module::with('levels')
            ->orderBy('sort')
            ->get()
            ->each(function ($module) {
                return $this->prepareModules($module);
            });
        // luego se podria crear grupos mediante algun modulo, de momento se pasan los id de manera directa
        $group_basic = Module::with('levels')
            ->whereIn('id', [7, 1, 6, 17, 18, 5, 14])
            ->orderBy('sort')
            ->get()
            ->each(function ($module) {
                return $this->prepareModules($module);
            });
        $group_hotel = Module::with('levels')
            ->whereIn('id', [7, 1, 6, 17, 18, 5, 14, 8, 4])
            ->orderBy('sort')
            ->get()
            ->each(function ($module) {
                return $this->prepareModules($module);
            });
        $group_pharmacy = Module::with('levels')
            ->whereIn('id', [7, 1, 6, 17, 18, 5, 14, 8, 4])
            ->orderBy('sort')
            ->get()
            ->each(function ($module) {
                return $this->prepareModules($module);
            });
        $group_restaurant = Module::with('levels')
            ->whereIn('id', [7, 1, 6, 17, 18, 5, 14, 8, 4])
            ->orderBy('sort')
            ->get()
            ->each(function ($module) {
                return $this->prepareModules($module);
            });
        $group_hotel_apps = Module::with('levels')
            ->whereIn('id', [15])
            ->orderBy('sort')
            ->get()
            ->each(function ($module) {
                return $this->prepareModules($module);
            });
        $group_pharmacy_apps = Module::with('levels')
            ->whereIn('id', [19])
            ->orderBy('sort')
            ->get()
            ->each(function ($module) {
                return $this->prepareModules($module);
            });
        $group_restaurant_apps = Module::with('levels')
            ->whereIn('id', [23])
            ->orderBy('sort')
            ->get()
            ->each(function ($module) {
                return $this->prepareModules($module);
            });
        $supply_module = Module::where('value', 'supplies')->first();
        if ($supply_module) {
            $supply_module_id = $supply_module->id;
            $group_supply_apps = Module::with('levels')
                ->whereIn('id', [$supply_module_id])
                ->orderBy('sort')
                ->get()
                ->each(function ($module) {
                    return $this->prepareModules($module);
                });
        }

        $config = Configuration::first();

        $certificate_admin = $config->certificate;
        $soap_username = $config->soap_username;
        $soap_password = $config->soap_password;
        $regex_password_client = $config->regex_password_client;

        return compact(
            'group_all',
            'url_base',
            'plans',
            'types',
            'modules',
            'apps',
            'certificate_admin',
            'soap_username',
            'soap_password',
            'group_basic',
            'group_hotel',
            'group_pharmacy',
            'group_restaurant',
            'group_hotel_apps',
            'group_pharmacy_apps',
            'regex_password_client',
            'group_restaurant_apps'
        );
    }

    private function prepareModules(Module $module): Module
    {
        $levels = [];
        foreach ($module->levels as $level) {
            array_push($levels, [
                'id' => "{$module->id}-{$level->id}",
                'description' => $level->description,
                'module_id' => $level->module_id,
                'is_parent' => false,
            ]);
        }
        unset($module->levels);
        $module->is_parent = true;
        $module->childrens = $levels;
        return $module;
    }
    public function deletePdfsFile(Request $request)
    {
        $days = $request->days;
        $directories = array_map('basename', glob(storage_path('app' . DIRECTORY_SEPARATOR . 'tenancy' . DIRECTORY_SEPARATOR . 'tenants' . DIRECTORY_SEPARATOR . '*'), GLOB_ONLYDIR));
        $folders_to_search = [
            'pdf',
            'sale_note',
            'quotation',
            'purchase',
            'purchase_order',
            'order_note',
            'expense',
            'download_tray_pdf',
        ];

        $total_files = 0;
        $deleted_files = 0;
        $cutoff_date = time() - ($days * 86400); // Convertir días a segundos

        foreach ($directories as $directory) {
            $tenant_path = storage_path('app' . DIRECTORY_SEPARATOR . 'tenancy' . DIRECTORY_SEPARATOR . 'tenants' . DIRECTORY_SEPARATOR . $directory);

            foreach ($folders_to_search as $folder) {
                $folder_path = $tenant_path . DIRECTORY_SEPARATOR . $folder;

                if (is_dir($folder_path)) {
                    list($found, $deleted) = $this->processOldPdfFiles($folder_path, $cutoff_date, $request->has('delete') && $request->delete === true);
                    $total_files += $found;
                    $deleted_files += $deleted;
                }
            }
        }

        return [
            'success' => true,
            'message' => "Se encontraron {$total_files} archivos PDF con antigüedad mayor a {$days} días" .
                ($deleted_files > 0 ? " y se eliminaron {$deleted_files} archivos." : "."),
            'total_files_found' => $total_files,
            'total_files_deleted' => $deleted_files,
        ];
    }

    /**
     * Procesa archivos PDF más antiguos que la fecha de corte y opcionalmente los elimina
     * 
     * @param string $directory Directorio a buscar
     * @param int $cutoff_date Timestamp de la fecha de corte
     * @param bool $delete Si es true, elimina los archivos
     * @return array [archivos_encontrados, archivos_eliminados]
     */
    private function processOldPdfFiles($directory, $cutoff_date, $delete = false)
    {
        $found_count = 0;
        $deleted_count = 0;

        $dir_iterator = new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($dir_iterator, \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $extension = strtolower(pathinfo($file->getPathname(), PATHINFO_EXTENSION));
                $file_time = $file->getMTime();

                if ($extension === 'pdf' && $file_time < $cutoff_date) {
                    $found_count++;

                    if ($delete) {
                        try {
                            if (file_exists($file->getPathname()) && unlink($file->getPathname())) {
                                $deleted_count++;
                            }
                        } catch (\Exception $e) {
                            \Log::error("Error al eliminar archivo: {$file->getPathname()} - " . $e->getMessage());
                        }
                    }
                }
            }
        }

        return [$found_count, $deleted_count];
    }

    public function deleteFolders(Request $request)
    {
        $directories = $request->folders;
        foreach ($directories as $directory) {
            $path = storage_path('app' . DIRECTORY_SEPARATOR . 'tenancy' . DIRECTORY_SEPARATOR . 'tenants' . DIRECTORY_SEPARATOR . $directory);
            $this->deleteDirectory($path);
        }
        return [
            'success' => true,
            'message' => 'Carpetas eliminadas correctamente',
        ];
    }
    private function deleteDirectory($path)
    {
        if (is_dir($path)) {
            $files = scandir($path);
            foreach ($files as $file) {
                if ($file != "." && $file != "..") {
                    $this->deleteDirectory($path . DIRECTORY_SEPARATOR . $file);
                }
            }
            if (!rmdir($path)) {
                throw new \Exception("Failed to remove directory: $path");
            }
        } else {
            if (!unlink($path)) {
                throw new \Exception("Failed to remove file: $path");
            }
        }
    }
    public function getTenantNotClients()
    {
        $records = Client::all()->transform(function ($row) {
            return [
                'id' => $row->id,
                'uuid' => $row->hostname->website->uuid,
            ];
        });
        $record = $records->first()["uuid"];
        $prefix = explode('_', $record)[0];
        $directories = array_map('basename', glob(storage_path('app' . DIRECTORY_SEPARATOR . 'tenancy' . DIRECTORY_SEPARATOR . 'tenants' . DIRECTORY_SEPARATOR . '*'), GLOB_ONLYDIR));
        $directories_to_delete = array_diff($directories, $records->pluck('uuid')->toArray());
        $directories_to_delete =  array_filter($directories_to_delete, function ($directory) use ($prefix) {
            return strpos($directory, $prefix) === 0;
        });
        $directories_to_delete = array_values($directories_to_delete);
        $directories_to_delete = array_map(function ($directory) {
            return ['name' => $directory, 'selected' => false];
        }, $directories_to_delete);

        return [
            "records" => $records,
            "directories" => $directories,
            "directories_to_delete" => $directories_to_delete
        ];
    }

    public function simpleRecords(Request $request)
    {
        $clients = Client::query();


        return new ClientSimpleCollection($clients->paginate(20));
    }
    public function records(Request $request)
    {
        $column = $request->input('column');
        $value = $request->input('value');
        $show_all = $request->input('show_all');
        $filters = $this->prepareFiltersAndDates($request);
        $query = Client::latest();

        if (!empty($column) && !empty($value)) {
            if ($column == 'hostname') {
                $query->whereHas('hostname', function ($query) use ($value) {
                    $query->where('fqdn', 'like', "%{$value}%");
                });
            } else if ($column == 'name') {
                $query->where('name', 'like', "%{$value}%")
                    ->orWhere('number', 'like', "%{$value}%");
            } else {
                $query->where($column, 'like', "%{$value}%");
            }
        }
        $page_count = config('tenant.items_per_page');
        if ($show_all) {
            $page_count = 200;
        }
        $paginatedRecords = $query->paginate($page_count);


        $paginatedRecords->getCollection()->transform(function ($row) use ($filters) {
            $tenancy = app(Environment::class);
            $tenancy->tenant($row->hostname->website);

            $row->count_doc = DB::connection('tenant')
                ->table('configurations')
                ->first();
            if ($row->count_doc) {
                $row->count_doc = $row->count_doc->quantity_documents;
            }
            if($filters['document_type_id'] != "0") {
                $row->total_sale_date = DB::connection('tenant')
                    ->table('documents')
                    ->whereBetween('date_of_issue', [$filters['date_start'], $filters['date_end']])
                    ->where('document_type_id', $filters['document_type_id'])
                    ->whereIn('state_type_id', ['01', '05', '03', '13'])
                    ->sum('total');
            }else{
                // Sumar documentos con state_type_id válido
                $total_sum = DB::connection('tenant')
                    ->table('documents')
                    ->whereBetween('date_of_issue', [$filters['date_start'], $filters['date_end']])
                    ->whereIn('state_type_id', ['01', '05', '03', '13'])
                    ->sum('total');
                
                // Restar documentos con document_type_id = 07
                $total_subtract = DB::connection('tenant')
                    ->table('documents')
                    ->whereBetween('date_of_issue', [$filters['date_start'], $filters['date_end']])
                    ->where('document_type_id', '07')
                    ->whereIn('state_type_id', ['01', '05', '03', '13'])
                    ->sum('total');
                
                $row->total_sale_date = $total_sum - $total_subtract;
            }
            $company = DB::connection('tenant')
                ->table('companies')
                ->first();
            $row->soap_type = $company->soap_type_id;
            $row->certificate_due = $company->certificate_due;

            $row->count_user = DB::connection('tenant')
                ->table('users')
                ->count();
            $row->count_item = DB::connection('tenant')
                ->table('items')
                ->count();
            $row->count_sales_notes = DB::connection('tenant')
                ->table('sale_notes')
                ->count();

            $quantity_pending_documents = $this->getQuantityPendingDocuments();
            $row->document_regularize_shipping = $quantity_pending_documents['document_regularize_shipping'];
            $row->document_not_sent = $quantity_pending_documents['document_not_sent'];
            $row->document_to_be_canceled = $quantity_pending_documents['document_to_be_canceled'];
            $row->monthly_sales_total = 0;

            if ($row->start_billing_cycle) {
                $start_end_date = DocumentHelper::getStartEndDateForFilterDocument($row->start_billing_cycle);
                $init = $start_end_date['start_date'];
                $end = $start_end_date['end_date'];

                $row->count_doc_month = DB::connection('tenant')->table('documents')->whereBetween('date_of_issue', [$init, $end])->count();
                $row->count_sales_notes_month = DB::connection('tenant')->table('sale_notes')->whereBetween('date_of_issue', [$init, $end])->count();

                if ($row->count_sales_notes_month > 0) {
                    if ($row->count_sales_notes != $row->count_sales_notes_month) {
                        $row->count_sales_notes = DB::connection('tenant')
                            ->table('configurations')
                            ->where('id', 1)
                            ->update([
                                'quantity_sales_notes' => $row->count_sales_notes_month
                            ]);
                    }
                }
                $row->count_sales_notes = DB::connection('tenant')
                    ->table('sale_notes')
                    ->count();

                $client_helper = new ClientHelper();
                $row->monthly_sales_total = $client_helper->getSalesTotal($init->format('Y-m-d'), $end->format('Y-m-d'), $row->plan);
            }

            $row->quantity_establishments = $this->getQuantityRecordsFromTable('establishments');

            $this->clearCacheTenant($row->hostname->website->uuid);
            $row->uuid = $row->hostname->website->uuid;
            return $row;
        });

        return new ClientCollection($paginatedRecords);
    }


    /**
     * Versión optimizada del método records con mejoras de rendimiento
     *
     * Optimizaciones aplicadas:
     * - Reduce consultas N+1 mediante batch processing
     * - Minimiza cambios de conexión entre tenants
     * - Usa cache para datos estáticos
     * - Agrupa consultas similares
     * - Optimiza queries con selects específicos
     *
     * @param Request $request
     * @return ClientCollection
     */
    public function records_optimized(Request $request)
    {
        $column = $request->input('column');
        $value = $request->input('value');
        $show_all = $request->input('show_all');
        $filters = $this->prepareFiltersAndDates($request);

        // Construir query base
        $query = Client::latest();

        // Aplicar filtros de búsqueda
        if (!empty($column) && !empty($value)) {
            if ($column == 'hostname') {
                $query->whereHas('hostname', function ($query) use ($value) {
                    $query->where('fqdn', 'like', "%{$value}%");
                });
            } else if ($column == 'name') {
                $query->where('name', 'like', "%{$value}%")
                    ->orWhere('number', 'like', "%{$value}%");
            } else {
                $query->where($column, 'like', "%{$value}%");
            }
        }

        $page_count = config('tenant.items_per_page');
        if ($show_all) {
            $page_count = 200;
        }

        // Obtener registros paginados con eager loading
        $paginatedRecords = $query->with(['hostname.website'])->paginate($page_count);

        // Precalcular datos para todos los clientes en el batch
        $clientsData = $this->batchProcessClientsData($paginatedRecords->items(), $filters);

        // Transformar registros usando datos precalculados
        $paginatedRecords->getCollection()->transform(function ($row, $index) use ($clientsData) {
            // Asignar todos los datos precalculados
            $clientData = $clientsData[$index] ?? [];

            $row->count_doc = $clientData['count_doc'] ?? null;
            $row->total_sale_date = $clientData['total_sale_date'] ?? 0;
            $row->soap_type = $clientData['soap_type'] ?? null;
            $row->certificate_due = $clientData['certificate_due'] ?? null;
            $row->count_user = $clientData['count_user'] ?? 0;
            $row->count_item = $clientData['count_item'] ?? 0;
            $row->count_sales_notes = $clientData['count_sales_notes'] ?? 0;
            $row->document_regularize_shipping = $clientData['document_regularize_shipping'] ?? 0;
            $row->document_not_sent = $clientData['document_not_sent'] ?? 0;
            $row->document_to_be_canceled = $clientData['document_to_be_canceled'] ?? 0;
            $row->monthly_sales_total = $clientData['monthly_sales_total'] ?? 0;
            $row->count_doc_month = $clientData['count_doc_month'] ?? null;
            $row->count_sales_notes_month = $clientData['count_sales_notes_month'] ?? null;
            $row->quantity_establishments = $clientData['quantity_establishments'] ?? 0;

            // Limpiar cache y asignar UUID
            $this->clearCacheTenant($row->hostname->website->uuid);
            $row->uuid = $row->hostname->website->uuid;

            return $row;
        });

        return new ClientCollection($paginatedRecords);
    }

    /**
     * Procesa datos de múltiples clientes en batch para optimizar rendimiento
     *
     * @param array $clients
     * @param array $filters
     * @return array
     */
    private function batchProcessClientsData(array $clients, array $filters)
    {
        $clientsData = [];
        $tenancy = app(Environment::class);

        foreach ($clients as $index => $client) {
            try {
                // Cambiar a tenant del cliente
                $tenancy->tenant($client->hostname->website);

                // Obtener todos los datos necesarios con queries optimizadas
                $clientsData[$index] = $this->getOptimizedClientData($client, $filters);

            } catch (\Exception $e) {
                // En caso de error, proporcionar valores por defecto
                $clientsData[$index] = $this->getDefaultClientData();
            }
        }

        return $clientsData;
    }

    /**
     * Obtiene datos del cliente de forma optimizada con mínimas consultas
     *
     * @param Client $client
     * @param array $filters
     * @return array
     */
    private function getOptimizedClientData($client, $filters)
    {
        $data = [];

        // Query 1: Obtener configuración y count_doc en una sola consulta
        $config = DB::connection('tenant')
            ->table('configurations')
            ->select('quantity_documents')
            ->first();

        $data['count_doc'] = $config->quantity_documents ?? null;

        // Query 2: Obtener datos de company
        $company = DB::connection('tenant')
            ->table('companies')
            ->select('soap_type_id', 'certificate_due')
            ->first();

        $data['soap_type'] = $company->soap_type_id ?? null;
        $data['certificate_due'] = $company->certificate_due ?? null;

        // Query 3: Calcular total_sale_date según filtros
        if ($filters['document_type_id'] != "0") {
            $data['total_sale_date'] = DB::connection('tenant')
                ->table('documents')
                ->whereBetween('date_of_issue', [$filters['date_start'], $filters['date_end']])
                ->where('document_type_id', $filters['document_type_id'])
                ->whereIn('state_type_id', ['01', '05', '03', '13'])
                ->sum('total');
        } else {
            // Usar una sola query con CASE WHEN para sumar y restar
            $result = DB::connection('tenant')
                ->table('documents')
                ->whereBetween('date_of_issue', [$filters['date_start'], $filters['date_end']])
                ->whereIn('state_type_id', ['01', '05', '03', '13'])
                ->select(DB::raw('
                    SUM(CASE WHEN document_type_id != "07" THEN total ELSE 0 END) -
                    SUM(CASE WHEN document_type_id = "07" THEN total ELSE 0 END) as total_sale
                '))
                ->first();

            $data['total_sale_date'] = $result->total_sale ?? 0;
        }

        // Query 4: Obtener todos los counts en una sola query usando subqueries
        $counts = DB::connection('tenant')
            ->table(DB::raw('(SELECT 1) as dummy'))
            ->select(DB::raw('
                (SELECT COUNT(*) FROM users) as count_user,
                (SELECT COUNT(*) FROM items) as count_item,
                (SELECT COUNT(*) FROM sale_notes) as count_sales_notes,
                (SELECT COUNT(*) FROM establishments) as quantity_establishments
            '))
            ->first();

        $data['count_user'] = $counts->count_user ?? 0;
        $data['count_item'] = $counts->count_item ?? 0;
        $data['count_sales_notes'] = $counts->count_sales_notes ?? 0;
        $data['quantity_establishments'] = $counts->quantity_establishments ?? 0;

        // Query 5: Obtener documentos pendientes en una sola query
        $pendingDocs = DB::connection('tenant')
            ->table('documents')
            ->select(DB::raw('
                SUM(CASE WHEN state_type_id = "01" AND regularize_shipping = 1 THEN 1 ELSE 0 END) as document_regularize_shipping,
                SUM(CASE WHEN state_type_id IN ("01", "03") AND date_of_issue <= "' . date('Y-m-d') . '" THEN 1 ELSE 0 END) as document_not_sent,
                SUM(CASE WHEN state_type_id = "13" THEN 1 ELSE 0 END) as document_to_be_canceled
            '))
            ->first();

        $data['document_regularize_shipping'] = $pendingDocs->document_regularize_shipping ?? 0;
        $data['document_not_sent'] = $pendingDocs->document_not_sent ?? 0;
        $data['document_to_be_canceled'] = $pendingDocs->document_to_be_canceled ?? 0;

        // Procesar datos mensuales si existe start_billing_cycle
        $data['monthly_sales_total'] = 0;
        $data['count_doc_month'] = null;
        $data['count_sales_notes_month'] = null;

        if ($client->start_billing_cycle) {
            $start_end_date = DocumentHelper::getStartEndDateForFilterDocument($client->start_billing_cycle);
            $init = $start_end_date['start_date'];
            $end = $start_end_date['end_date'];

            // Query 6: Obtener counts mensuales en una sola query
            $monthlyCounts = DB::connection('tenant')
                ->table(DB::raw('(SELECT 1) as dummy'))
                ->select(DB::raw('
                    (SELECT COUNT(*) FROM documents WHERE date_of_issue BETWEEN "' . $init . '" AND "' . $end . '") as count_doc_month,
                    (SELECT COUNT(*) FROM sale_notes WHERE date_of_issue BETWEEN "' . $init . '" AND "' . $end . '") as count_sales_notes_month
                '))
                ->first();

            $data['count_doc_month'] = $monthlyCounts->count_doc_month ?? 0;
            $data['count_sales_notes_month'] = $monthlyCounts->count_sales_notes_month ?? 0;

            // Actualizar configuración si es necesario (solo si count_sales_notes_month > 0)
            if ($data['count_sales_notes_month'] > 0 &&
                $data['count_sales_notes'] != $data['count_sales_notes_month']) {
                DB::connection('tenant')
                    ->table('configurations')
                    ->where('id', 1)
                    ->update(['quantity_sales_notes' => $data['count_sales_notes_month']]);

                // Actualizar el valor en data
                $data['count_sales_notes'] = $data['count_sales_notes_month'];
            }

            // Calcular total de ventas mensuales
            $client_helper = new ClientHelper();
            $data['monthly_sales_total'] = $client_helper->getSalesTotal(
                $init->format('Y-m-d'),
                $end->format('Y-m-d'),
                $client->plan
            );
        }

        return $data;
    }

    /**
     * Retorna datos por defecto en caso de error
     *
     * @return array
     */
    private function getDefaultClientData()
    {
        return [
            'count_doc' => null,
            'total_sale_date' => 0,
            'soap_type' => null,
            'certificate_due' => null,
            'count_user' => 0,
            'count_item' => 0,
            'count_sales_notes' => 0,
            'document_regularize_shipping' => 0,
            'document_not_sent' => 0,
            'document_to_be_canceled' => 0,
            'monthly_sales_total' => 0,
            'count_doc_month' => null,
            'count_sales_notes_month' => null,
            'quantity_establishments' => 0,
        ];
    }

    public function clearCache(Request $request)
    {
        $this->flushCacheTenant($request->uuid);
        return [
            'success' => true,
            'message' => 'Cache limpiada correctamente'
        ];
    }

    /**
     *
     * @param  string $table
     * @return int
     */
    private function getQuantityRecordsFromTable($table)
    {
        return DB::connection('tenant')->table($table)->count();
    }


    private function getQuantityPendingDocuments()
    {

        return [
            'document_regularize_shipping' => DB::connection('tenant')->table('documents')->where('state_type_id', '01')->where('regularize_shipping', true)->count(),
            'document_not_sent' => DB::connection('tenant')->table('documents')->whereIn('state_type_id', ['01', '03'])->where('date_of_issue', '<=', date('Y-m-d'))->count(),
            'document_to_be_canceled' => DB::connection('tenant')->table('documents')->where('state_type_id', '13')->count(),
        ];
    }

    /**
     * Obtiene las notificaciones globales de todos los tenants en producción
     * Método optimizado que calcula los totales de las 3 tipos de notificaciones
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function global_notifications()
    {
        try {
            $tenancy = app(Environment::class);

            // Obtener solo clientes activos en producción (soap_type = '02')
            $clients = Client::where('active', false)
                ->with(['hostname.website'])
                ->get();

            $totals = [
                'document_not_sent' => 0,
                'document_regularize_shipping' => 0,
                'document_to_be_canceled' => 0,
                'clients_with_notifications' => 0,
            ];

            $clientsWithIssues = [];

            foreach ($clients as $client) {
                try {
                    // Solo procesar clientes en producción
                    $tenancy->tenant($client->hostname->website);

                    // Obtener soap_type del tenant
                    $company = DB::connection('tenant')
                        ->table('companies')
                        ->select('soap_type_id')
                        ->first();

                    // Solo contar si está en producción (soap_type_id = '02')
                    if ($company && $company->soap_type_id == '02' || $company->soap_type_id == '01') {
                        // Query optimizada que obtiene las 3 notificaciones en una sola consulta
                        $notifications = DB::connection('tenant')
                            ->table('documents')
                            ->select(DB::raw('
                                SUM(CASE WHEN state_type_id IN ("01", "03") AND date_of_issue <= "' . date('Y-m-d') . '" THEN 1 ELSE 0 END) as document_not_sent,
                                SUM(CASE WHEN state_type_id = "01" AND regularize_shipping = 1 THEN 1 ELSE 0 END) as document_regularize_shipping,
                                SUM(CASE WHEN state_type_id = "13" THEN 1 ELSE 0 END) as document_to_be_canceled
                            '))
                            ->first();

                        $clientNotifications = [
                            'document_not_sent' => $notifications->document_not_sent ?? 0,
                            'document_regularize_shipping' => $notifications->document_regularize_shipping ?? 0,
                            'document_to_be_canceled' => $notifications->document_to_be_canceled ?? 0,
                        ];

                        // Sumar a los totales
                        $totals['document_not_sent'] += $clientNotifications['document_not_sent'];
                        $totals['document_regularize_shipping'] += $clientNotifications['document_regularize_shipping'];
                        $totals['document_to_be_canceled'] += $clientNotifications['document_to_be_canceled'];

                        // Si el cliente tiene alguna notificación, agregarlo a la lista
                        $hasNotifications = $clientNotifications['document_not_sent'] > 0 ||
                                          $clientNotifications['document_regularize_shipping'] > 0 ||
                                          $clientNotifications['document_to_be_canceled'] > 0;

                        if ($hasNotifications) {
                            $totals['clients_with_notifications']++;
                            $clientsWithIssues[] = [
                                'id' => $client->id,
                                'name' => $client->name,
                                'number' => $client->number,
                                'hostname' => $client->hostname->fqdn ?? '',
                                'notifications' => $clientNotifications,
                            ];
                        }
                    }

                } catch (\Exception $e) {
                    // Si hay error con un tenant, continuar con el siguiente
                    continue;
                }
            }

            return response()->json([
                'success' => true,
                'totals' => $totals,
                'clients_with_issues' => $clientsWithIssues,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener notificaciones globales: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function changeShowEyesInLogin(Request $request)
    {
        $client = Client::findOrFail($request->id);
        $client->show_eyes_in_login = $request->show_eyes_in_login;
        $client->save();
        return response()->json(['success' => true, 'message' => 'Mostrar ojos en login actualizado correctamente']);
    }

    

    public function record($id)
    {
        $client = Client::findOrFail($id);
        $tenancy = app(Environment::class);
        $tenancy->tenant($client->hostname->website);
        $user_id = 1;
        // Se buscan los valores en las tablas de los clientes, luego se compara con las tablas de admin para mostrar
        // correctamente la seleccion en la seccion de modulos de permisos
        $modules = DB::connection('tenant')
            ->table('modules')
            ->where('modules.order_menu', '<=', 13)
            ->join('module_user', 'module_user.module_id', '=', 'modules.id')
            ->where('module_user.user_id', $user_id)
            ->select('modules.value as value')
            ->get()
            ->pluck('value');
        $client->modules = DB::connection('system')
            ->table('modules')
            ->wherein('value', $modules)
            ->select('id')
            ->distinct()
            ->get()
            ->pluck('id');

        // Se buscan los valores en las tablas de los clientes, luego se compara con las tablas de admin para mostrar
        // correctamente la seleccion en la seccion de modulos de permisos
        // Apps
        $apps = DB::connection('tenant')
            ->table('modules')
            ->where('modules.order_menu', '>', 13)
            ->join('module_user', 'module_user.module_id', '=', 'modules.id')
            ->where('module_user.user_id', $user_id)
            ->select('modules.value as value')
            ->get()
            ->pluck('value');

        $client->apps = DB::connection('system')
            ->table('modules')
            ->wherein('value', $apps)
            ->select('id')
            ->distinct()
            ->get()
            ->pluck('id');

        // Se buscan los valores en las tablas de los clientes, luego se compara con las tablas de admin para mostrar
        // correctamente la seleccion en la seccion de modulos de permisos
        $levels = DB::connection('tenant')
            ->table('module_level_user')
            ->where('module_level_user.user_id', $user_id)
            ->join('module_levels', 'module_levels.id', '=', 'module_level_user.module_level_id')
            ->get()
            ->pluck('value');

        $client->levels = DB::connection('system')
            ->table('module_levels')
            ->wherein('value', $levels)
            ->select('id')
            ->distinct()
            ->get()
            ->pluck('id');

        $config = DB::connection('tenant')
            ->table('configurations')
            ->first();

        // $client->config_system_env = $config->config_system_env;

        $company = DB::connection('tenant')
            ->table('companies')
            ->first();

        $client->soap_send_id = $company->soap_send_id;
        $client->soap_type_id = $company->soap_type_id;
        $client->soap_username = $company->soap_username;
        $client->pse = $company->pse;
        $client->soap_password = $company->soap_password;
        $client->config_system_env = $client->config_system_env;

        $client->soap_url = $company->soap_url;
        $client->certificate = $company->certificate;
        $client->number = $company->number;
        $client->is_rus = $company->is_rus;


        return new ClientResource($client);
    }

    public function charts()
    {
        $records = Client::where('active', 0)->get();
        $count_documents = [];
        foreach ($records as $row) {
            $tenancy = app(Environment::class);
            $tenancy->tenant($row->hostname->website);
            for ($i = 1; $i <= 12; $i++) {
                $date_initial = Carbon::parse(date('Y') . '-' . $i . '-1');
                $year_before = Carbon::now()->subYear()->format('Y');
                // $date_final = Carbon::parse(date('Y') . '-' . $i . '-' . cal_days_in_month(CAL_GREGORIAN, $i, $year_before));
                $date_final = $date_initial->copy()->endOfMonth();
                $count_documents[] = [
                    'client' => $row->number,
                    'month' => $i,
                    'count' => $row->count_doc = DB::connection('tenant')
                        ->table('documents')
                        ->whereBetween('date_of_issue', [$date_initial, $date_final])
                        ->count()
                ];
            }
        }

        $total_documents = collect($count_documents)->sum('count');

        $groups_by_month = collect($count_documents)->groupBy('month');
        $labels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Set', 'Oct', 'Nov', 'Dic'];
        $documents_by_month = [];
        foreach ($groups_by_month as $month => $group) {
            $documents_by_month[] = $group->sum('count');
        }

        $line = [
            'labels' => $labels,
            'data' => $documents_by_month
        ];

        return compact('line', 'total_documents');
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function update(Request $request)
    {
        /**
         * @var Collection $valueModules
         * @var Collection $valueLevels
         */
        $user_id = 1;
        $array_modules = [];
        $array_levels = [];

        $soap_send_id = ($request->has('soap_send_id')) ? $request->soap_send_id : null;
        $smtp_host = ($request->has('smtp_host')) ? $request->smtp_host : null;
        $smtp_password = ($request->has('smtp_password')) ? $request->smtp_password : null;
        $smtp_port = ($request->has('smtp_port')) ? $request->smtp_port : null;
        $smtp_user = ($request->has('smtp_user')) ? $request->smtp_user : null;
        $smtp_encryption = ($request->has('smtp_encryption')) ? $request->smtp_encryption : null;
        $is_rus = ($request->has('is_rus')) ? $request->is_rus : false;
        try {

            $temp_path = $request->input('temp_path');

            $name_certificate = $request->input('certificate');

            if ($temp_path) {

                try {
                    $password = $request->input('password_certificate');
                    if ($soap_send_id == "03") {
                        $name = 'certificate_smart.pem';
                    } else {
                        $name = 'certificate_' . $request->input('number') . '.pem';
                    }
                    $certificate_is_pem = $name_certificate && str_ends_with($name_certificate, '.pem');
                    $pfx = file_get_contents($temp_path);
                    if ($soap_send_id !== "03" && !$certificate_is_pem) {
                        $pem = GenerateCertificate::typePEM($pfx, $password);
                    } else {
                        $pem = $pfx;
                    }
                    if (!file_exists(storage_path('app' . DIRECTORY_SEPARATOR . 'certificates'))) {
                        mkdir(storage_path('app' . DIRECTORY_SEPARATOR . 'certificates'));
                    }
                    file_put_contents(storage_path('app' . DIRECTORY_SEPARATOR . 'certificates' . DIRECTORY_SEPARATOR . $name), $pem);
                    $name_certificate = $name;
                } catch (Exception $e) {
                    return [
                        'success' => false,
                        'message' => $e->getMessage()
                    ];
                }
            } else {
                if ($soap_send_id == "03") {
                    $name = 'certificate_smart.pem';
                    if (!file_exists(storage_path('app' . DIRECTORY_SEPARATOR . 'certificates'))) {
                        mkdir(storage_path('app' . DIRECTORY_SEPARATOR . 'certificates'));
                    }
                    if (file_exists(storage_path('app' . DIRECTORY_SEPARATOR . 'certificates' . DIRECTORY_SEPARATOR . $name))) {
                        $name_certificate = $name;
                    } else {
                        $path_smart = storage_path('smart' . DIRECTORY_SEPARATOR . 'certificate_smart.pem');
                        if (file_exists($path_smart)) {
                            $pem = file_get_contents($path_smart);
                            file_put_contents(storage_path('app' . DIRECTORY_SEPARATOR . 'certificates' . DIRECTORY_SEPARATOR . $name), $pem);
                            $name_certificate = $name;
                        }
                    }
                }
            }


            $client = Client::findOrFail($request->id);

            $client
                ->setSmtpHost($smtp_host)
                ->setSmtpPort($smtp_port)
                ->setSmtpUser($smtp_user)
                //    ->setSmtpPassword($smtp_password)
                ->setSmtpEncryption($smtp_encryption);
            if (!empty($smtp_password)) {
                $client->setSmtpPassword($smtp_password);
            }
            if ($soap_send_id == "03") {
                $client->cert_smart = true;
            } else {
                $client->cert_smart = false;
            }
            $client->plan_id = $request->plan_id;
            $client->users = $request->input('users');
            $client->password = $request->input('password_sunat');
            $client->password_cdt = $request->input('password_cdt');
            $client->config_system_env = $request->input('config_system_env');
            $client->save();

            $plan = Plan::find($request->plan_id);

            $tenancy = app(Environment::class);
            $tenancy->tenant($client->hostname->website);
            $clientData = [
                'plan' => json_encode($plan),
                'config_system_env' => $request->config_system_env,
                'limit_documents' => $plan->limit_documents,
                'smtp_host' => $client->smtp_host,
                'smtp_port' => $client->smtp_port,
                'smtp_user' => $client->smtp_user,
                'smtp_password' => $client->smtp_password,
                'smtp_encryption' => $client->smtp_encryption,
            ];
            if (empty($client->smtp_password)) unset($clientData['smtp_password']);
            DB::connection('tenant')
                ->table('configurations')
                ->where('id', 1)
                ->update($clientData);
            if ($plan) {
                $limit_users = $plan->limit_users;
                $limit_documents = $plan->limit_documents;
                $establishments_unlimited = $plan->establishments_unlimited;
                if ($limit_users > 0) {
                    DB::connection('tenant')
                        ->table('configurations')
                        ->where('id', 1)
                        ->update(['limit_users' => true, 'locked_users' => true]);
                    $client->locked_users = true;
                }

                // if ($limit_documents > 0) {
                //     DB::connection('tenant')
                //         ->table('configurations')
                //         ->where('id', 1)
                //         ->update(['limit_documents' => true]);
                //     $client->locked_emission = true;
                // }
                if (!$establishments_unlimited) {
                    $client->locked_create_establishments = true;
                    DB::connection('tenant')
                        ->table('configurations')
                        ->where('id', 1)
                        ->update(['locked_create_establishments' => true]);
                    // $client->locked_tenant = true;
                }
                $client->save();
            }

            DB::connection('tenant')
                ->table('companies')
                ->where('id', 1)
                ->update([
                    'soap_type_id' => $request->soap_type_id,
                    'soap_send_id' => $request->soap_send_id == '03' ? '02' : $request->soap_send_id,
                    'soap_username' => $request->soap_username,
                    'soap_password' =>   $request->soap_password,
                    'soap_url' => $request->soap_send_id == '03' ? $this->soap_url : $request->soap_url,
                    'certificate' => $name_certificate,
                    'is_rus' => $is_rus,
                    'pse_url' => 'https://consultaperu.pe',
                ]);

            // if ($is_rus) {
            //     DB::connection('tenant')
            //         ->table('cat_document_types')
            //         ->where('id', '01')
            //         ->update([
            //             'active' => true,
            //         ]);
            // } else {
            //     DB::connection('tenant')
            //         ->table('cat_document_types')
            //         ->where('id', '01')
            //         ->update([
            //             'active' => true,
            //         ]);
            // }
            //modules
            DB::connection('tenant')
                ->table('module_user')
                ->where('user_id', $user_id)
                ->delete();
            DB::connection('tenant')
                ->table('module_level_user')
                ->where('user_id', $user_id)
                ->delete();

            // Obtenemos los value de las tablas
            $valueModules = DB::connection('system')
                ->table('modules')
                ->wherein('id', $request->modules)
                ->get()
                ->pluck('value');
            $valueLevels = DB::connection('system')
                ->table('module_levels')
                ->wherein('id', $request->levels)
                ->get()
                ->pluck('value');

            // Obtenemos el modelo del modulo, asi se obtendrá el id del elemento
            DB::connection('tenant')
                ->table('modules')
                ->wherein('value', $valueModules)
                ->select(
                    'id as module_id',
                    DB::raw(" CONCAT($user_id) as user_id")
                )
                ->get()
                ->transform(function ($module) use (&$array_modules) {
                    $array_modules[] = (array)$module;
                });
            DB::connection('tenant')
                ->table('module_levels')
                ->wherein('value', $valueLevels)
                ->select(
                    'id as module_level_id',
                    DB::raw(" CONCAT($user_id) as user_id")
                )
                ->get()
                ->transform(function ($level) use (&$array_levels) {
                    $array_levels[] = (array)$level;
                });
            // Se actualiza las tablas de permisos
            DB::connection('tenant')
                ->table('module_user')
                ->insert($array_modules);
            DB::connection('tenant')
                ->table('module_level_user')
                ->insert($array_levels);

            // Actualiza el modulo de farmacia.
            $config = (array)DB::connection('tenant')
                ->table('configurations')
                ->first();
            $config['is_pharmacy'] = (self::EnablePharmacy($user_id)) ? 1 : 0;
            DB::connection('tenant')
                ->table('configurations')
                ->update($config);

            if ($request->create_restaurant == true || $request->create_restaurant == 1) {
                DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=0');
                DB::connection('tenant')->table('ordens')->delete();
                DB::connection('tenant')->table('orden_item')->delete();
                DB::connection('tenant')->table('users')->whereNotNull('area_id')->delete();
                DB::connection('tenant')->table('workers_type')->delete();
                DB::connection('tenant')->table('status_orders')->delete();
                DB::connection('tenant')->table('tables')->delete();
                DB::connection('tenant')->table('status_table')->delete();
                DB::connection('tenant')->table('areas')->delete();
                DB::connection('tenant')->table('status_orders')->insert([
                    ['id' => '1', 'description' => 'Pago sin verificar', 'created_at' => now()],
                    ['id' => '2', 'description' => 'Pago verificado', 'created_at' => now()],
                    ['id' => '3', 'description' => 'Despachado', 'created_at' => now()],
                    ['id' => '4', 'description' => 'Confirmado por el cliente', 'created_at' => now()],
                ]);
                DB::connection('tenant')->table('status_table')->insert([
                    ['id' => '1', 'description' => 'Libre', 'active' => true, 'created_at' => now()],
                    ['id' => '2', 'description' => 'Ocupado', 'active' => true, 'created_at' => now()],
                    ['id' => '3', 'description' => 'mantenimiento', 'active' => true, 'created_at' => now()],
                ]);
                DB::connection('tenant')->table('areas')->insert([
                    ['id' => '1', 'description' => 'Cocina', 'copies' => null, 'printer' => null, 'active' => true, 'created_at' => now()],
                    ['id' => '2', 'description' => 'Salon 1', 'copies' => null, 'printer' => null, 'active' => true, 'created_at' => now()],
                    ['id' => '3', 'description' => 'Salon 2', 'copies' => null, 'printer' => null, 'active' => true, 'created_at' => now()],
                    ['id' => '4', 'description' => 'Caja', 'copies' => null, 'printer' => null, 'active' => true, 'created_at' => now()],
                ]);
                DB::connection('tenant')->table('tables')->insert([
                    ['id' => '1', 'number' => '1', 'status_table_id' => '1', 'area_id' => '2', 'created_at' => now()],
                    ['id' => '2', 'number' => '2', 'status_table_id' => '1', 'area_id' => '2', 'created_at' => now()],
                    ['id' => '3', 'number' => '3', 'status_table_id' => '1', 'area_id' => '2', 'created_at' => now()],
                    ['id' => '4', 'number' => '4', 'status_table_id' => '1', 'area_id' => '2', 'created_at' => now()],
                    ['id' => '5', 'number' => '5', 'status_table_id' => '1', 'area_id' => '2', 'created_at' => now()],
                ]);
                DB::connection('tenant')->table('workers_type')->insert([
                    ['id' => '1', 'description' => 'Cajera', 'active' => true, 'created_at' => now()],
                    ['id' => '2', 'description' => 'Cocinero', 'active' => true, 'created_at' => now()],
                    ['id' => '3', 'description' => 'Mozo', 'active' => true, 'created_at' => now()],
                ]);
                DB::connection('tenant')->table('users')->insert([
                    ['name' => 'Cajera', 'type' => "seller", 'number' => "1", 'pin' => $this->generatePIN(4), 'worker_type_id' => '1', 'area_id' => '1', 'created_at' => now()],
                ]);
                DB::connection('tenant')->table('users')->insert([
                    ['name' => 'Cocinero', 'type' => "seller", 'number' => "2", 'pin' => $this->generatePIN(4), 'worker_type_id' => '2', 'area_id' => '2', 'created_at' => now()],
                ]);
                DB::connection('tenant')->table('users')->insert([
                    ['name' => 'Mozo', 'type' => "seller", 'number' => "3", 'pin' => $this->generatePIN(4), 'worker_type_id' => '3', 'area_id' => '3', 'created_at' => now()],
                ]);
                DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=1');
            }
            // $this->clearCacheTenant($client->hostname->website->uuid);
            CacheTrait::flushCacheTenant($client->hostname->website->uuid);
            return [
                'success' => true,
                'message' => 'Cliente Actualizado satisfactoriamente',
                'modules' => $array_modules,
                'levels' => $array_levels,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Devuelve la informacion si el modulo de farmacia esta habilitado o no para activar la configuracion
     * correspondiente
     *
     * @param int $user_id
     *
     * @return bool
     */
    private function generatePIN($digits = 4)
    {
        $i = 0;
        $pin = "";
        while ($i < $digits) {
            $pin .= mt_rand(0, 9);
            $i++;
        }


        return $pin;
    }
    public static function EnablePharmacy($user_id = 0)
    {
        $modulo_id = DB::connection('tenant')
            ->table('modules')
            ->where('value', 'digemid')
            ->first()->id;
        $modulo = DB::connection('tenant')
            ->table('module_user')
            ->where('module_id', $modulo_id)
            ->where('user_id', $user_id)
            ->first();

        return ($modulo == null) ? false : true;
    }

    public function store(ClientRequest $request)
    {

        $temp_path = $request->input('temp_path');
        $configuration = Configuration::first();
        $soap_send_id = $request->input('soap_send_id');
        $name_certificate = $configuration->certificate;
        $is_rus = $request->input('is_rus');
        if ($temp_path) {
            try {
                $password = $request->input('password_certificate');
                $name = 'certificate_' . 'admin_tenant' . '.pem';
                $certificate_is_pem = $name_certificate && str_ends_with($name_certificate, '.pem');
                $pfx = file_get_contents($temp_path);
                if ($soap_send_id !== "03" && !$certificate_is_pem) {
                    $pem = GenerateCertificate::typePEM($pfx, $password);
                } else {
                    $pem = $pfx;
                }
                if (!file_exists(storage_path('app' . DIRECTORY_SEPARATOR . 'certificates'))) {
                    mkdir(storage_path('app' . DIRECTORY_SEPARATOR . 'certificates'));
                }
                file_put_contents(storage_path('app' . DIRECTORY_SEPARATOR . 'certificates' . DIRECTORY_SEPARATOR . $name), $pem);
                $name_certificate = $name;
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        } else {
            if ($soap_send_id == "03") {
                $name = 'certificate_smart.pem';
                if (!file_exists(storage_path('app' . DIRECTORY_SEPARATOR . 'certificates'))) {
                    mkdir(storage_path('app' . DIRECTORY_SEPARATOR . 'certificates'));
                }
                if (file_exists(storage_path('app' . DIRECTORY_SEPARATOR . 'certificates' . DIRECTORY_SEPARATOR . $name))) {
                    $name_certificate = $name;
                } else {
                    $path_smart = storage_path('smart' . DIRECTORY_SEPARATOR . 'certificate_smart.pem');
                    if (file_exists($path_smart)) {
                        $pem = file_get_contents($path_smart);
                        file_put_contents(storage_path('app' . DIRECTORY_SEPARATOR . 'certificates' . DIRECTORY_SEPARATOR . $name), $pem);
                        $name_certificate = $name;
                    }
                }
            }
        }


        $subDom = strtolower($request->input('subdomain'));
        $uuid = config('tenant.prefix_database') . '_' . $subDom;
        $fqdn = $subDom . '.' . config('tenant.app_url_base');

        $website = new Website();
        $hostname = new Hostname();
        $this->validateWebsite($uuid, $website);

        DB::connection('system')->getPdo()->inTransaction();
        try {
            $website->uuid = $uuid;
            app(WebsiteRepository::class)->create($website);
            $hostname->fqdn = $fqdn;
            app(HostnameRepository::class)->attach($hostname, $website);

            $tenancy = app(Environment::class);
            $tenancy->tenant($website);

            $token = str_random(50);
            $client = new Client();
            $client->hostname_id = $hostname->id;
            $client->token = $token;
            $client->email = strtolower($request->input('email'));
            $client->name = $request->input('name');
            $client->number = $request->input('number');
            $client->plan_id = $request->input('plan_id');
            $client->config_system_env = $request->config_system_env;
            $client->locked_emission = $request->input('locked_emission');
            $client->users = $request->input('users');
            $client->password = $request->input('password_sunat');
            $client->password_cdt = $request->input('password_cdt');
            $client->save();
            if ($request['create_restaurant'] == true) {
                //     DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=0');
                DB::connection('tenant')->table('ordens')->delete();
                DB::connection('tenant')->table('orden_item')->delete();
                DB::connection('tenant')->table('users')->whereNotNull('area_id')->delete();
                DB::connection('tenant')->table('workers_type')->delete();
                DB::connection('tenant')->table('status_orders')->delete();
                DB::connection('tenant')->table('tables')->delete();
                DB::connection('tenant')->table('status_table')->delete();
                DB::connection('tenant')->table('areas')->delete();
                DB::connection('tenant')->table('status_orders')->insert([
                    ['id' => '1', 'description' => 'Pago sin verificar', 'created_at' => now()],
                    ['id' => '2', 'description' => 'Pago verificado', 'created_at' => now()],
                    ['id' => '3', 'description' => 'Despachado', 'created_at' => now()],
                    ['id' => '4', 'description' => 'Confirmado por el cliente', 'created_at' => now()],
                ]);
                DB::connection('tenant')->table('status_table')->insert([
                    ['id' => '1', 'description' => 'Libre', 'active' => true, 'created_at' => now()],
                    ['id' => '2', 'description' => 'Ocupado', 'active' => true, 'created_at' => now()],
                    ['id' => '3', 'description' => 'mantenimiento', 'active' => true, 'created_at' => now()],
                ]);
                DB::connection('tenant')->table('areas')->insert([
                    ['id' => '1', 'description' => 'Cocina', 'copies' => null, 'printer' => null, 'active' => true, 'created_at' => now()],
                    ['id' => '2', 'description' => 'Salon 1', 'copies' => null, 'printer' => null, 'active' => true, 'created_at' => now()],
                    ['id' => '3', 'description' => 'Salon 2', 'copies' => null, 'printer' => null, 'active' => true, 'created_at' => now()],
                    ['id' => '4', 'description' => 'Caja', 'copies' => null, 'printer' => null, 'active' => true, 'created_at' => now()],
                ]);
                DB::connection('tenant')->table('tables')->insert([
                    ['id' => '1', 'number' => '1', 'status_table_id' => '1', 'area_id' => '2', 'created_at' => now()],
                    ['id' => '2', 'number' => '2', 'status_table_id' => '1', 'area_id' => '2', 'created_at' => now()],
                    ['id' => '3', 'number' => '3', 'status_table_id' => '1', 'area_id' => '2', 'created_at' => now()],
                    ['id' => '4', 'number' => '4', 'status_table_id' => '1', 'area_id' => '2', 'created_at' => now()],
                    ['id' => '5', 'number' => '5', 'status_table_id' => '1', 'area_id' => '2', 'created_at' => now()],
                ]);
                DB::connection('tenant')->table('workers_type')->insert([
                    ['id' => '1', 'description' => 'Cajera', 'active' => true, 'created_at' => now()],
                    ['id' => '2', 'description' => 'Cocinero', 'active' => true, 'created_at' => now()],
                    ['id' => '3', 'description' => 'Mozo', 'active' => true, 'created_at' => now()],
                ]);
                DB::connection('tenant')->table('users')->insert([
                    ['name' => 'Cajera', 'type' => "seller", 'number' => "1", 'pin' => $this->generatePIN(4), 'worker_type_id' => '1', 'area_id' => '1', 'created_at' => now()],
                ]);
                DB::connection('tenant')->table('users')->insert([
                    ['name' => 'Cocinero', 'type' => "seller", 'number' => "2", 'pin' => $this->generatePIN(4), 'worker_type_id' => '2', 'area_id' => '2', 'created_at' => now()],
                ]);
                DB::connection('tenant')->table('users')->insert([
                    ['name' => 'Mozo', 'type' => "seller", 'number' => "3", 'pin' => $this->generatePIN(4), 'worker_type_id' => '3', 'area_id' => '3', 'created_at' => now()],
                ]);
            }
            DB::connection('system')->commit();
        } catch (Exception $e) {
            DB::connection('system')->rollBack();
            app(HostnameRepository::class)->delete($hostname, true);
            app(WebsiteRepository::class)->delete($website, true);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
        $name = $request->input('name');
        $trade_name = $request->input('trade_name');
        if ($is_rus && $trade_name) {
            $name = $trade_name;
        }
        DB::connection('tenant')->table('companies')->insert([
            'identity_document_type_id' => '6',
            'number' => $request->input('number'),
            'name' => $request->input('name'),
            'trade_name' => $name,
            'is_rus' => $is_rus,

            'soap_type_id' => $request->soap_type_id,
            'soap_send_id' => $request->soap_send_id,
            'soap_username' => $request->soap_username,
            'soap_password' => $request->soap_password,
            'soap_url' => $request->soap_url,
            'certificate' => $name_certificate,
            'pse_url' => 'https://consultaperu.pe',
            // 'cert_smart' => $request->soap_send_id == '03' ? true : false,
        ]);

        $plan = Plan::findOrFail($request->input('plan_id'));
        if ($plan) {
            $limit_users = $plan->limit_users;
            $limit_documents = $plan->limit_documents;
            $establishments_unlimited = $plan->establishments_unlimited;

            if ($limit_users > 0) {
                DB::connection('tenant')
                    ->table('configurations')
                    ->where('id', 1)
                    ->update(['limit_users' => true, 'locked_users' => true]);
                $client->locked_users = true;
            }
            if ($limit_documents > 0) {
                DB::connection('tenant')
                    ->table('configurations')
                    ->where('id', 1)
                    ->update(['limit_documents' => true]);
                $client->locked_emission = true;
            }
            if (!$establishments_unlimited) {
                DB::connection('tenant')
                    ->table('configurations')
                    ->where('id', 1)
                    ->update(['locked_create_establishments' => true]);
                $client->locked_create_establishments = true;
            }
            $client->save();
        }
        $http = config('tenant.force_https') == true ? 'https://' : 'http://';

        DB::connection('tenant')->table('configurations')->insert([
            'change_name_click_item' => 1,
            'send_auto' => true,
            'locked_emission' => $request->input('locked_emission'),
            'locked_tenant' => false,
            'locked_users' => false,
            'limit_documents' => $plan->limit_documents,
            'limit_users' => $plan->limit_users,
            'ticket_single_shipment' => 1,
            'plan' => json_encode($plan),
            'quotation_allow_seller_generate_sale' => 1,
            'allow_edit_unit_price_to_seller' => 1,
            'seller_can_create_product' => 1,
            'seller_can_view_balance' => 1,
            'show_ticket_50' => 1,
            'show_ticket_58' => 1,
            'affect_all_documents' => 1,
            'seller_can_generate_sale_opportunities' => 1,
            'item_name_pdf_description' => 1,
            'amount_plastic_bag_taxes' => 0.50,
            'product_only_location' => 1,
            'show_logo_by_establishment' => 1,
            'shipping_time_days_voided' => 3,
            'include_igv' => 1,
            'date_time_start' => date('Y-m-d H:i:s'),
            'quantity_documents' => 0,
            'config_system_env' => $request->config_system_env,
            'login' => json_encode([
                'type' => 'image',
                'image' => $http . $fqdn . '/images/fondo-5.svg',
                'position_form' => 'right',
                'show_logo_in_form' => false,
                'position_logo' => 'top-left',
                'show_socials' => false,
                'facebook' => null,
                'twitter' => null,
                'instagram' => null,
                'linkedin' => null,
            ]),
            'visual' => json_encode([
                'bg' => 'white',
                'header' => 'light',
                'navbar' => 'fixed',
                'sidebars' => 'light',
                'sidebar_theme' => 'white'
            ]),
            'skin_id' => 2,
            'top_menu_a_id' => 1,
            'top_menu_b_id' => 15,
            'top_menu_c_id' => 76,
            'quantity_sales_notes' => 0,
            'taxed_igv_visible_nv' => true
        ]);


        $establishment_id = DB::connection('tenant')->table('establishments')->insertGetId([
            'description' => 'Oficina Principal',
            'country_id' => 'PE',
            'department_id' => '15',
            'province_id' => '1501',
            'district_id' => '150101',
            'address' => '-',
            'email' => $request->input('email'),
            'telephone' => '-',
            'code' => '0000'
        ]);
        $person = DB::connection('tenant')
            ->table('persons')
            ->where('number', '99999999')
            ->where('type', 'customers')
            ->first();
        if ($person && $person->id) {
            $person_id = $person->id;
            DB::connection('tenant')
                ->table('establishments')
                ->where('id', $establishment_id)
                ->update(['customer_id' => $person_id]);
        }




        DB::connection('tenant')->table('warehouses')->insertGetId([
            'establishment_id' => $establishment_id,
            'description' => 'Almacén Oficina Principal',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::connection('tenant')->table('series')->insert([
            ['establishment_id' => 1, 'document_type_id' => '01', 'number' => 'FTR1'],
            ['establishment_id' => 1, 'document_type_id' => '03', 'number' => 'BLT1'],
            ['establishment_id' => 1, 'document_type_id' => '07', 'number' => 'FTC1'],
            ['establishment_id' => 1, 'document_type_id' => '07', 'number' => 'BLC1'],
            ['establishment_id' => 1, 'document_type_id' => '08', 'number' => 'FTD1'],
            ['establishment_id' => 1, 'document_type_id' => '08', 'number' => 'BLD1'],
            ['establishment_id' => 1, 'document_type_id' => '20', 'number' => 'R001'],
            ['establishment_id' => 1, 'document_type_id' => '09', 'number' => 'TR01'],
            ['establishment_id' => 1, 'document_type_id' => '40', 'number' => 'P001'],
            ['establishment_id' => 1, 'document_type_id' => '80', 'number' => 'NV01'],
            ['establishment_id' => 1, 'document_type_id' => '04', 'number' => 'L001'],
            ['establishment_id' => 1, 'document_type_id' => 'U2', 'number' => 'NIA1'],
            ['establishment_id' => 1, 'document_type_id' => 'U3', 'number' => 'NSA1'],
            ['establishment_id' => 1, 'document_type_id' => 'U4', 'number' => 'NTA1'],
            ['establishment_id' => 1, 'document_type_id' => 'PD', 'number' => 'PD01'],
            ['establishment_id' => 1, 'document_type_id' => 'COT', 'number' => 'COT1'],
            ['establishment_id' => 1, 'document_type_id' => '31', 'number' => 'VT01'],
            ['establishment_id' => 1, 'document_type_id' => 'OCB', 'number' => 'OCB1'],
            ['establishment_id' => 1, 'document_type_id' => 'OCS', 'number' => 'OCS1'],
        ]);


        $user_id = DB::connection('tenant')->table('users')->insert([
            'name' => 'Administrador',
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
            'api_token' => $token,
            'establishment_id' => $establishment_id,
            'type' => $request->input('type'),
            'locked' => true,
            'permission_edit_cpe' => true,
            'last_password_update' => date('Y-m-d H:i:s'),
        ]);

        if ($is_rus) {
            $serie = DB::connection('tenant')->table('series')->where('document_type_id', '03')
                ->where('establishment_id', $establishment_id)
                ->first();
            if ($serie) {
                DB::connection('tenant')->table('users')->where('id', $user_id)->update([
                    'document_id' => '03',
                    'series_id' => $serie->id,
                ]);
            }
            DB::connection('tenant')->table('establishments')->where('id', $establishment_id)
                ->update([
                    'template_pdf' => 'rus',
                    'template_ticket_pdf' => 'rus',
                ]);
            DB::connection('tenant')->table('cat_document_types')
                ->where('id', '01')
                ->update([
                    'active' => false,
                ]);
        }


        DB::connection('tenant')->table('cash')->insert([
            'user_id' => $user_id,
            'date_opening' => date('Y-m-d'),
            'time_opening' => date('H:i:s'),
            'beginning_balance' => 0,
            'final_balance' => 0,
            'income' => 0,
            'state' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);


        if ($request->input('type') == 'admin') {
            $array_modules = [];
            $array_levels = [];
            foreach ($request->modules as $module) {
                array_push($array_modules, [
                    'module_id' => $module,
                    'user_id' => $user_id
                ]);
            }
            foreach ($request->levels as $level) {
                array_push($array_levels, [
                    'module_level_id' => $level,
                    'user_id' => $user_id
                ]);
            }
            DB::connection('tenant')->table('module_user')->insert($array_modules);
            DB::connection('tenant')->table('module_level_user')->insert($array_levels);

            $this->insertAppModules($user_id);
        } else {
            DB::connection('tenant')->table('module_user')->insert([
                ['module_id' => 1, 'user_id' => $user_id],
                ['module_id' => 3, 'user_id' => $user_id],
                ['module_id' => 5, 'user_id' => $user_id],
            ]);
        }

        //en company | logo  bg_logo solo nombre de archivo, en favicon ruta completa


        //en establishment | logo ruta completa

        $bg_logo_name = null;
        $logo_name = null;
        $favicon_name = null;
        $tenant_logo = $configuration->tenant_logo;
        $tenant_bg_logo = $configuration->tenant_bg_logo;

        // Validar y extraer nombre del archivo de fondo
        if ($tenant_bg_logo && !empty($tenant_bg_logo)) {
            $bg_logo_split = explode('/', $tenant_bg_logo);
            $bg_logo_name = end($bg_logo_split); // Usar end() en lugar de count() - 1
        }

        $tenant_favicon = $configuration->tenant_favicon;

        if ($tenant_favicon && !empty($tenant_favicon)) {
            $favicon_split = explode('/', $tenant_favicon);
            $favicon_name = end($favicon_split); // Usar end() en lugar de count() - 1
        }

        // Actualizar logo en establishment si existe
        if ($tenant_logo && !empty($tenant_logo)) {
            DB::connection('tenant')->table('establishments')->where('id', $establishment_id)->update([
                'logo' => $tenant_logo,
            ]);
        }

        // Extraer nombre del archivo de logo
        if ($tenant_logo && !empty($tenant_logo)) {
            $logo_split = explode('/', $tenant_logo);
            $logo_name = end($logo_split); // Usar end() en lugar de count() - 1
        }

        // Actualizar company usando update() en lugar de save()
        if ($bg_logo_name || $logo_name || $tenant_favicon) {
            $updateData = [];

            if ($bg_logo_name) {
                $updateData['bg_default'] = $bg_logo_name;
            }
            if ($logo_name) {
                $updateData['logo'] = $logo_name;
            }
            if ($favicon_name) {
                $updateData['favicon'] = $favicon_name;
            }

            DB::connection('tenant')->table('companies')->where('id', 1)->update($updateData);
        }

        return [
            'success' => true,
            'message' => 'Cliente Registrado satisfactoriamente'
        ];
    }

    public function validateWebsite($uuid, $website)
    {

        $exists = $website::where('uuid', $uuid)->first();

        if ($exists) {
            throw new Exception("El subdominio ya se encuentra registrado");
        }
    }


    /**
     *
     * Registrar modulos de la app al usuario principal
     *
     * @param  int $user_id
     * @return void
     */
    private function insertAppModules($user_id)
    {
        $all_app_modules = AppModule::get()->map(function ($row) use ($user_id) {
            return [
                'app_module_id' => $row->id,
                'user_id' => $user_id,
            ];
        })->toArray();

        DB::connection('tenant')->table('app_module_user')->insert($all_app_modules);
    }
    public function setCertificateDue(Request $request)
    {

        $client = Client::findOrFail($request->id);
        $tenancy = app(Environment::class);
        $tenancy->tenant($client->hostname->website);

        DB::connection('tenant')->table('companies')->update([
            'certificate_due' => $request->certificate_due,
        ]);

        return [
            'success' => true,
            'message' => 'Vencimiento de certificado actualizado con exito'
        ];
    }

    public function renewPlan(Request $request)
    {

        $client = Client::findOrFail($request->id);
        $tenancy = app(Environment::class);
        $tenancy->tenant($client->hostname->website);

        DB::connection('tenant')->table('billing_cycles')->insert([
            'date_time_start' => date('Y-m-d H:i:s'),
            'renew' => true,
            'quantity_documents' => DB::connection('tenant')->table('configurations')->where('id', 1)->first()->quantity_documents,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::connection('tenant')->table('configurations')->where('id', 1)->update(['quantity_documents' => 0]);
        DB::connection('tenant')->table('configurations')->where('id', 1)->update(['quantity_sales_notes' => 0]);

        $this->clearCacheTenant($client->hostname->website->uuid);
        return [
            'success' => true,
            'message' => 'Plan renovado con exito'
        ];
    }
    public function lockedItem(Request $request)
    {

        $client = Client::findOrFail($request->id);
        $client->locked_items = $request->locked_items;
        $client->save();

        $tenancy = app(Environment::class);
        $tenancy->tenant($client->hostname->website);
        DB::connection('tenant')->table('configurations')->where('id', 1)->update(['locked_items' => $client->locked_items]);

        return [
            'success' => true,
            'message' => ($client->locked_items) ? 'Limitar creación de productos activado' : 'Limitar creación de productos desactivado'
        ];
    }

    public function lockedUser(Request $request)
    {

        $client = Client::findOrFail($request->id);
        $client->locked_users = $request->locked_users;
        $client->save();

        $tenancy = app(Environment::class);
        $tenancy->tenant($client->hostname->website);
        DB::connection('tenant')->table('configurations')->where('id', 1)->update(['locked_users' => $client->locked_users]);

        $this->clearCacheTenant($client->hostname->website->uuid);
        return [
            'success' => true,
            'message' => ($client->locked_users) ? 'Limitar creación de usuarios activado' : 'Limitar creación de usuarios desactivado'
        ];
    }


    public function lockedEmission(Request $request)
    {

        $client = Client::findOrFail($request->id);
        $client->locked_emission = $request->locked_emission;
        $client->save();

        $tenancy = app(Environment::class);
        $tenancy->tenant($client->hostname->website);
        DB::connection('tenant')->table('configurations')->where('id', 1)->update(['locked_emission' => $client->locked_emission]);

        $this->clearCacheTenant($client->hostname->website->uuid);

        return [
            'success' => true,
            'message' => ($client->locked_emission) ? 'Limitar emisión de documentos activado' : 'Limitar emisión de documentos desactivado'
        ];
    }



    public function activeTenant(Request $request)
    {

        $client = Client::findOrFail($request->id);
        $client->active = $request->active;
        $client->save();

        // $tenancy = app(Environment::class);
        // $tenancy->tenant($client->hostname->website);
        // DB::connection('tenant')->table('configurations')->where('id', 1)->update(['active' => $client->active]);

        return [
            'success' => true,
            'message' => ($client->active) ? 'Cuenta desactivada' : 'Cuenta activa'
        ];
    }
    public function lockedTenant(Request $request)
    {

        $client = Client::findOrFail($request->id);
        $client->locked_tenant = $request->locked_tenant;
        $client->save();

        $tenancy = app(Environment::class);
        $tenancy->tenant($client->hostname->website);
        $uuid = $client->hostname->website->uuid;
        DB::connection('tenant')->table('configurations')->where('id', 1)->update(['locked_tenant' => $client->locked_tenant]);
        $this->clearCacheTenant($uuid);

        return [
            'success' => true,
            'message' => ($client->locked_tenant) ? 'Cuenta bloqueada' : 'Cuenta desbloqueada'
        ];
    }


    public function config_system_env(Request $request)
    {
        $client = Client::findOrFail($request->id);
        $client->config_system_env = $request->config_system_env_tenant;
        $client->save();
        return [
            'success' => true,
            'message' => ($client->config_system_env) ? 'Entorno bloqueada' : 'Entorno desbloqueada'
        ];
    }
    public function checkInputValidateDelete(Client $client, $input_validate)
    {

        if ($input_validate === $client->name || $input_validate === $client->number) {
            return $this->generalResponse(true);
        }

        return $this->generalResponse(false, 'El valor ingresado no coincide con el nombre o número de ruc de la empresa.');
    }


    /**
     *
     * Eliminar cliente
     *
     * @param  int $id
     * @param  string $input_validate
     * @return array
     */
    public function destroy($id, $input_validate)
    {
        $client = Client::find($id);

        $check_input_validate_delete = $this->checkInputValidateDelete($client, $input_validate);
        if (!$check_input_validate_delete['success']) return $check_input_validate_delete;

        if ($client->locked) {
            return [
                'success' => false,
                'message' => 'Cliente bloqueado, no puede eliminarlo'
            ];
        }

        $hostname = Hostname::find($client->hostname_id);
        $website = Website::find($hostname->website_id);

        app(HostnameRepository::class)->delete($hostname, true);
        app(WebsiteRepository::class)->delete($website, true);

        return [
            'success' => true,
            'message' => 'Cliente eliminado con éxito'
        ];
    }

    public function password($id)
    {
        $client = Client::find($id);
        $website = Website::find($client->hostname->website_id);
        $tenancy = app(Environment::class);
        $tenancy->tenant($website);
        DB::connection('tenant')->table('users')
            ->where('type', 'admin')
            ->orderBy('id', 'asc')
            ->limit(1)
            ->update(['password' => bcrypt($client->number)]);


        return [
            'success' => true,
            'message' => 'Clave cambiada con éxito'
        ];
    }

    public function endBillingCycle(Request $request)
    {
        $client = Client::findOrFail($request->id);
        $end_billing_cycle = $request->end_billing_cycle;
        if ($end_billing_cycle) {
            $client->end_billing_cycle = $end_billing_cycle;
            $today = date('Y-m-d');
            if ($today >= $end_billing_cycle) {
                $request_renew = new Request();
                $request_renew->merge([
                    'id' => $request->id,
                    'locked_tenant' => '1'
                ]);
                $this->lockedTenant($request_renew);
            }
        } else {
            $client->end_billing_cycle = null;
        }
        $client->save();
        return [
            'success' => true,
            'message' => 'Ciclo de termino definido.'
        ];
    }
    public function startBillingCycle(Request $request)
    {
        $client = Client::findOrFail($request->id);
        $start_billing_cycle = $request->start_billing_cycle;
        if ($start_billing_cycle) {
            $client->start_billing_cycle = $start_billing_cycle;
            $client->save();
        } else {
            $client->start_billing_cycle = null;
        }

        $client->save();
        $message  =  'Ciclo de Facturacion definido.';
        return [
            'success' => true,
            'message' => $message
        ];
    }

    public function upload(Request $request)
    {
        if ($request->hasFile('file')) {
            $new_request = [
                'file' => $request->file('file'),
                'type' => $request->input('type'),
            ];

            return $this->upload_certificate($new_request);
        }
        return [
            'success' => false,
            'message' => 'Error al subir file.',
        ];
    }

    public function upload_certificate($request)
    {
        $file = $request['file'];
        $type = $request['type'];

        $temp = tempnam(sys_get_temp_dir(), $type);
        file_put_contents($temp, file_get_contents($file));

        $mime = mime_content_type($temp);
        $data = file_get_contents($temp);

        return [
            'success' => true,
            'data' => [
                'filename' => $file->getClientOriginalName(),
                'temp_path' => $temp,
                //'temp_image' => 'data:' . $mime . ';base64,' . base64_encode($data)
            ]
        ];
    }


    /**
     *
     * @param  Request $request
     * @return array
     */
    public function lockedByColumn(Request $request)
    {
        $column = $request->column;
        $client = Client::findOrFail($request->id);
        $client->{$column} = $request->{$column};
        $client->save();

        $tenancy = app(Environment::class);
        $tenancy->tenant($client->hostname->website);
        DB::connection('tenant')->table('configurations')->where('id', 1)->update([$column => $client->{$column}]);
        $this->clearCacheTenant($client->hostname->website->uuid);
        return $this->generalResponse(true, $client->{$column} ? 'Activado correctamente' : 'Desactivado correctamente');
    }

    public function clearCacheTenant($uuid)
    {
        $key1 = "tenant_{$uuid}_configuration";
        $key2 = "tenant_{$uuid}_public_config";

        Cache::forget($key1);
        Cache::forget($key2);
    }

    /**
     * Método optimizado para obtener registros de clientes con mejoras de rendimiento
     * Versión optimizada del método records_clients con las siguientes mejoras:
     * - Eliminación del problema N+1
     * - Optimización de consultas de base de datos
     * - Mejor manejo multitenant
     * - Uso eficiente de memoria con chunking
     * - Sistema de cache para datos estáticos
     *
     * @param Request $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function records_clients_optimized(Request $request)
    {
        // Preparar filtros y fechas
        $filters = $this->prepareFiltersAndDates($request);

        // Obtener datos estáticos cacheados
        $staticData = $this->getCachedStaticData();

        $results = new Collection();

        // Procesar según el tipo de consulta
        if ($request->id == "0") {
            // Procesar todos los clientes con chunking para optimizar memoria
            $this->processAllClientsOptimized($filters, $staticData, $results);
        } else {
            // Procesar un cliente específico
            $this->processSingleClientOptimized($request->id, $filters, $staticData, $results);
        }

        // Crear paginación optimizada manualmente
        $perPage = 50;
        $page = request()->get('page', 1);
        $paginatedData = $results->forPage($page, $perPage);

        return new LengthAwarePaginator(
            ClientDocumentCollection::make($paginatedData)->resolve(),
            $results->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * Preparar filtros y fechas para la consulta
     */
    private function prepareFiltersAndDates(Request $request): array
    {
        $period = $request->period_id;
        $d_start = null;
        $d_end = null;

        switch ($period) {
            case 'month':
                $d_start = Carbon::parse($request->month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($request->month_start . '-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'between_months':
                $d_start = Carbon::parse($request->month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($request->month_end . '-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'date':
                $d_start = $request->date_start;
                $d_end = $request->date_start;
                break;
            case 'between_dates':
                $d_start = $request->date_start;
                $d_end = $request->date_end;
                break;
        }

        return [
            'state_type_id' => $request->state_type_id,
            'document_type_id' => $request->document_type_id,
            'date_start' => $d_start,
            'date_end' => $d_end,
        ];
    }

    /**
     * Obtener datos estáticos cacheados para evitar consultas repetitivas
     */
    private function getCachedStaticData(): array
    {
        return [
            'document_types' => Cache::remember('cat_document_types', 3600, function () {
                return DB::table('cat_document_types')
                    ->where('active', '1')
                    ->pluck('description', 'id')
                    ->toArray();
            }),
            'state_types' => Cache::remember('state_types', 3600, function () {
                return DB::table('state_types')
                    ->pluck('description', 'id')
                    ->toArray();
            }),
        ];
    }

    /**
     * Procesar todos los clientes con optimizaciones
     */
    private function processAllClientsOptimized(array $filters, array $staticData, Collection $results): void
    {
        // Usar chunking para manejar grandes volúmenes de datos sin agotar memoria
        Client::with(['hostname.website'])
            ->latest()
            ->chunk(10, function ($clients) use ($filters, $staticData, $results) {
                $this->processTenantDocumentsInBatch($clients, $filters, $staticData, $results);
            });
    }

    /**
     * Procesar un cliente específico
     */
    private function processSingleClientOptimized(int $clientId, array $filters, array $staticData, Collection $results): void
    {
        $client = Client::with(['hostname.website'])->findOrFail($clientId);
        $this->processTenantDocuments($client, $filters, $staticData, $results, true);
    }

    /**
     * Procesar documentos de múltiples tenants en lote
     */
    private function processTenantDocumentsInBatch(Collection $clients, array $filters, array $staticData, Collection $results): void
    {
        foreach ($clients as $client) {
            $this->processTenantDocuments($client, $filters, $staticData, $results);
        }
    }

    /**
     * Procesar documentos de un tenant específico
     */
    private function processTenantDocuments($client, array $filters, array $staticData, Collection $results, bool $isSingleClient = false): void
    {
        try {
            $tenancy = app(Environment::class);
            $tenancy->tenant($client->hostname->website);

            // Cachear company por tenant para evitar consultas repetitivas
            $company = Cache::remember(
                "tenant_company_{$client->hostname->website->uuid}",
                1800, // 30 minutos
                function () {
                    return DB::connection('tenant')->table('companies')->first();
                }
            );

            if (!$company) {
                return; // Skip si no hay company
            }

            // Construir query optimizada con todos los filtros
            $documentsQuery = DB::connection('tenant')
                ->table('documents as d')
                ->leftJoin('voided_documents as vd', 'vd.document_id', '=', 'd.id')
                ->select([
                    'd.id',
                    'd.document_type_id',
                    'd.external_id',
                    'd.series',
                    'd.number',
                    'd.date_of_issue',
                    'd.customer',
                    'd.total',
                    'd.total_taxed',
                    'd.total_igv',
                    'd.state_type_id',
                    'vd.voided_id'
                ]);
            // Aplicar filtros
            $this->applyFiltersToQuery($documentsQuery, $filters);

            // Procesar documentos en chunks para optimizar memoria
            $documentsQuery->orderBy('d.date_of_issue', 'asc')->chunk(100, function ($documents) use ($client, $company, $staticData, $results, $isSingleClient) {
                foreach ($documents as $document) {
                    $formattedData = $this->formatDocumentData($document, $client, $company, $staticData, $isSingleClient);
                    $results->push($formattedData);
                }
            });
        } catch (Exception $e) {
            // Log error pero continuar con otros tenants
            Log::error("Error processing tenant {$client->hostname->fqdn}: " . $e->getMessage());
        }
    }

    /**
     * Aplicar filtros a la query de documentos
     */
    private function applyFiltersToQuery($query, array $filters): void
    {
        if ($filters['document_type_id'] != "0") {
            $query->where('d.document_type_id', '=', $filters['document_type_id']);
        }

        if ($filters['state_type_id'] != "0") {
            $query->where('d.state_type_id', '=', $filters['state_type_id']);
        }

        if ($filters['date_start'] && $filters['date_end']) {
            $query->whereBetween('d.date_of_issue', [$filters['date_start'], $filters['date_end']]);
        }

        // Agregar índices sugeridos para optimización:
        // CREATE INDEX idx_documents_filters ON documents(document_type_id, state_type_id, date_of_issue);
        // CREATE INDEX idx_documents_date ON documents(date_of_issue);
    }

    /**
     * Formatear datos del documento de manera eficiente
     */
    private function formatDocumentData($document, $client, $company, array $staticData, bool $isSingleClient = false)
    {
        // Decodificar JSON del customer una sola vez
        $customer = json_decode($document->customer);

        // Obtener descripciones de los datos cacheados en lugar de hacer queries
        $documentType = $staticData['document_types'][$document->document_type_id] ?? 'Unknown';
        $stateType = $staticData['state_types'][$document->state_type_id] ?? 'Unknown';

        // Preparar URL base una sola vez
        $baseUrl = $isSingleClient
            ? $client->hostname->fqdn
            : (strpos(url()->current(), 'https') !== false ? 'https://' : 'http://') . $client->hostname->fqdn;

        // Retornar objeto para que ClientDocumentCollection lo procese
        return (object)[
            "client_id" => $client->id,
            "hostname" => $baseUrl,
            "token" => $client->token,
            "company_name" => $company->name,
            "company_number" => $company->number,
            "document_type_id" => $document->document_type_id,
            "external_id" => $document->external_id,
            "document_id" => $document->id,
            "document_type" => $documentType,
            "series" => $document->series,
            "number" => $document->number,
            "date_of_issue" => $document->date_of_issue,
            "customer_name" => $customer->name ?? '',
            "customer_number" => $customer->number ?? '',
            "total" => $document->total,
            "total_taxed" => $document->total_taxed,
            "total_igv" => $document->total_igv,
            "state_type" => $stateType,
            "state_type_id" => $document->state_type_id,
            "voided_id" => $document->voided_id
        ];
    }

    /**
     * Crear paginación optimizada
     */
    private function buildOptimizedPagination(Collection $results, Request $request)
    {
        $perPage = 50;
        $page = $request->get('page', 1);
        $paginatedData = $results->forPage($page, $perPage);

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedData,
            $results->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query()
            ]
        );
    }
}

<?php

namespace App\Http\Controllers\Tenant;

use App\Models\Tenant\Company;
use App\Models\Tenant\SoapType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\CompanyRequest;
use App\Http\Resources\Tenant\CompanyResource;
use Illuminate\Http\Request;
use App\Http\Requests\Tenant\CompanyPseRequest;
use App\Http\Requests\Tenant\CompanyWhatsAppApiRequest;
use App\Models\System\Client;
use App\Models\System\Error;
use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\Configuration;
use App\Traits\CacheTrait;
use App\Traits\JobReportTrait;
use Carbon\Carbon;
use Hyn\Tenancy\Facades\TenancyFacade;
use Modules\Finance\Helpers\UploadFileHelper;
use Hyn\Tenancy\Models\Hostname;
use Illuminate\Support\Facades\Http;
use Ifsnop\Mysqldump as IMysqldump;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Class CompanyController
 *
 * @package App\Http\Controllers\Tenant
 * @mixin  Controller
 */
class CompanyController extends Controller
{
    use JobReportTrait, CacheTrait;


    public function downloadFiles(Request $request) {
        $company = Company::first();
        $date_start = Carbon::parse($request->date_start)->startOfDay();
        $date_end = Carbon::parse($request->date_end)->endOfDay();

        $files = Storage::disk('tenant')->allFiles();
        $zip = new \ZipArchive();
        $zip_name = storage_path('app/downloads/tenant_files_' . $company->number . '_' . date('Y-m-d_H-i-s') . '.zip');

        // Crear directorio si no existe
        if (!file_exists(storage_path('app/downloads'))) {
            mkdir(storage_path('app/downloads'), 0755, true);
        }

        if ($zip->open($zip_name, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            foreach ($files as $file) {
                $file_time = Storage::disk('tenant')->lastModified($file);
                $file_date = Carbon::createFromTimestamp($file_time);

                if ($file_date->between($date_start, $date_end)) {
                    $contents = Storage::disk('tenant')->get($file);
                    $zip->addFromString($file, $contents);
                }
            }
            
            $zip->close();
            
            return response()->download($zip_name)->deleteFileAfterSend(true);
        }

        return [
            'success' => false,
            'message' => 'No se pudo crear el archivo ZIP'
        ];
    }
    public function downloadAllInfoIndex(){
        



        return view('tenant.companies.info_download');
    }

    public function downloadAllInfoFixed($info_string)

    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $user = auth()->user();
        if($user->type != 'admin' && $user->type != 'superadmin'){
            return response()->json(['message' => 'La ruta no existe.'], 404);
        }

        $random_string = func_generate_random_string_with_day("a8b4c6d9e2f1h3i5j7k0");
        if($info_string != $random_string){
            return response()->json(['message' => 'La ruta no existe.'], 404);
        }
    
        $company = Company::first();
        $certificate = $company->certificate;

        $website = $this->getTenantWebsite();
        $database = $website->uuid;
        $dbConfig = config('database.connections.' . config('tenancy.db.system-connection-name', 'system'));
        $host = $dbConfig['host'];
        $username = $dbConfig['username'];
        $password = $dbConfig['password'];
        
        // Crear la ruta completa
        $backup_path = storage_path('app/backups/' . $database);
        if (!is_dir($backup_path)) {
            mkdir($backup_path, 0755, true);
        }

        // Crear backup de base de datos
        $tenant_dump = new IMysqldump\Mysqldump(
            'mysql:host=' . $host . ';dbname=' . $database, $username, $password
        );
        $tenant_dump->start("{$backup_path}/{$database}.sql");
        
        // Crear archivo ZIP
        $zip = new \ZipArchive();
        $zip_name = "{$backup_path}/{$database}_backup.zip";
        
        if ($zip->open($zip_name, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            // Agregar archivo SQL
            $zip->addFile("{$backup_path}/{$database}.sql", "{$database}.sql");
            
            // Agregar certificado si existe
            $cert_path = storage_path('app' . DIRECTORY_SEPARATOR . 'certificates' . DIRECTORY_SEPARATOR . $certificate);
            if ($certificate && file_exists($cert_path)) {
                $zip->addFile($cert_path, "certificates/{$certificate}");
            }
            
            $zip->close();
            
            // Eliminar archivo SQL temporal
            unlink("{$backup_path}/{$database}.sql");
            
            return response()->download($zip_name)->deleteFileAfterSend(true);
        }
        
        return [
            'success' => false,
            'message' => 'No se pudo crear el archivo ZIP'
        ];
    }
    public function downloadAllInfo($info_string)

    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $user = auth()->user();
        if($user->type != 'admin' && $user->type != 'superadmin'){
            return response()->json(['message' => 'La ruta no existe.'], 404);
        }

        $random_string = func_generate_random_string_with_day("a8b4c6d9e2f1h3i5j7k0");
        if($info_string != $random_string){
            return response()->json(['message' => 'La ruta no existe.'], 404);
        }
    
        $company = Company::first();
        $certificate = $company->certificate;

        $website = $this->getTenantWebsite();
        $database = $website->uuid;
        $dbConfig = config('database.connections.' . config('tenancy.db.system-connection-name', 'system'));
        $host = $dbConfig['host'];
        $username = $dbConfig['username'];
        $password = $dbConfig['password'];
        
        // Crear la ruta completa
        $backup_path = storage_path('app/backups/' . $database);
        if (!is_dir($backup_path)) {
            mkdir($backup_path, 0755, true);
        }

        // Crear backup de base de datos
        $tenant_dump = new IMysqldump\Mysqldump(
            'mysql:host=' . $host . ';dbname=' . $database, $username, $password
        );
        $tenant_dump->start("{$backup_path}/{$database}.sql");
        
        // Crear archivo ZIP
        $zip = new \ZipArchive();
        $zip_name = "{$backup_path}/{$database}_backup.zip";
        
        if ($zip->open($zip_name, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            // Agregar archivo SQL
            $zip->addFile("{$backup_path}/{$database}.sql", "{$database}.sql");
            
            // Agregar certificado si existe
            $cert_path = storage_path('app' . DIRECTORY_SEPARATOR . 'certificates' . DIRECTORY_SEPARATOR . $certificate);
            if ($certificate && file_exists($cert_path)) {
                $zip->addFile($cert_path, "certificates/{$certificate}");
            }
            
            $zip->close();
            
            // Eliminar archivo SQL temporal
            unlink("{$backup_path}/{$database}.sql");
            
            return response()->download($zip_name)->deleteFileAfterSend(true);
        }
        
        return [
            'success' => false,
            'message' => 'No se pudo crear el archivo ZIP'
        ];
    }
    public function msgToPay()
    {
        $tenant = TenancyFacade::tenant();
        $to_pay = false;
        
        if ($tenant) {
            $tenantId = $tenant->id;
            
            // Usar select() para obtener solo los campos necesarios
            $hostname = Hostname::select('id')
                ->where('website_id', $tenantId)
                ->first();
                
            if ($hostname) {
                // Usar select() para obtener solo el campo end_billing_cycle
                $client = DB::table('clients')
                    ->select('end_billing_cycle')
                    ->where('hostname_id', $hostname->id)
                    ->first();
                    
                if ($client && $client->end_billing_cycle) {
                    $now = Carbon::now();
                    // Verificar si faltan 2 días o menos para el fin del ciclo
                    if ($now->diffInDays($client->end_billing_cycle) <= 2) {
                        $to_pay = true;
                    }
                }
            }
        }
        
        // Solo cargar el campo necesario de Error
        $error = Error::select('msg_to_pay')->findOrFail(1);
        $msg_to_pay = $error->msg_to_pay;
        
        if ($msg_to_pay && strlen($msg_to_pay) > 0 && $to_pay) {
            return [
                'success' => true,
                'message' => $msg_to_pay,
                'to_pay' => $to_pay
            ];
        }

        return [
            'success' => false,
        ];
    }
    public function storePse(Request $request)
    {
        $company = Company::firstOrFail();
        $company->pse = $request->pse ?? false;
        $company->pse_token = $request->pse_token ?? null;
        $company->pse_url = $request->pse_url ?? "https://consultaperu.pe";
        $company->type_send_pse = $request->type_send_pse ? 1 : 2;
        if ($company->type_send_pse == 1) {
            $company->soap_send_id = '02';
        }
        if($company->type_send_pse == 1 && $company->pse == 1 && $company->soap_type_id == '01'){
            $company->soap_type_id = '02';
        }
        if($company->type_send_pse == 2 && $company->pse == 0){
            $company->soap_url = null;
            $company->soap_username = null;
            $company->soap_password = null;
        }
        $company->save();
        CacheTrait::clearCache('vc_company');

        return [
            'success' => true,
            'message' => 'Datos guardados correctamente'
        ];
    }
    public function recordPse()
    {
        $company = Company::firstOrFail();
        return [
            'pse' => $company->pse,
            'pse_token' => $company->pse_token,
            'pse_url' => $company->pse_url,
            'type_send_pse' => $company->type_send_pse == 1 ? true : false,
        ];
    }
    public function removeGetSendPse()
    {
        $configuration = Configuration::firstOrFail();
        if ($configuration->multi_company) {
            $companies = Company::all();
            foreach ($companies as $company) {
                if ($company->pse && $company->pse_token && $company->pse_url && $company->soap_url && $company->type_send_pse == 2) {
                    $company->certificate = null;
                    $company->soap_url = null;
                    $company->soap_username = null;
                    $company->soap_password = null;
                    $company->save();
                    CacheTrait::clearCache('vc_company');
                }
            }
            return [
                'success' => true,
                'message' => 'Datos eliminados correctamente'
            ];
        } else {
            $company = Company::firstOrFail();
            if ($company->pse && $company->pse_token && $company->pse_url && $company->soap_url && $company->type_send_pse == 2) {
                $company->certificate = null;
                $company->soap_url = null;
                $company->soap_username = null;
                $company->soap_password = null;
                $company->save();
                CacheTrait::clearCache('vc_company');
                return [
                    'success' => true,
                    'message' => 'Datos eliminados correctamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No se pudo eliminar los datos de PSE'
                ];
            }
        }
    }

    public function getSendPse()
    {
        $configuration = Configuration::firstOrFail();
        if ($configuration->multi_company) {
            $companies = Company::all();
            foreach ($companies as $company) {
                if ($company->pse && $company->pse_token && $company->pse_url) {
                    $number = $company->number;
                    $pse_url = $company->pse_url;
                    $pse_token = $company->pse_token;
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $pse_token,
                        'Ruc' => $number
                    ])->post($pse_url . '/api/pse/get_variables/sub_client', [
                        'number' => $number,
                    ]);
                    $response = $response->json();
                    if ($response['success']) {
                        $pse_url = $response['pse_url'];
                        $pse_username = $response['pse_username'];
                        $pse_password = $response['pse_password'];
                        $send_for_sunat = $response['send_for_sunat'];
                        $name = 'certificate_smart.pem';
                        if ($pse_url && $pse_username && $pse_password) {
                            $company->soap_url = $pse_url;
                            $company->soap_username = $pse_username;
                            $company->soap_password = $pse_password;
                            $company->certificate = $name;
                            $company->soap_send_id = $send_for_sunat ? '01' : '02';
                            if (!file_exists(storage_path('app' . DIRECTORY_SEPARATOR . 'certificates'))) {
                                mkdir(storage_path('app' . DIRECTORY_SEPARATOR . 'certificates'));
                            }

                            if (!file_exists(storage_path('app' . DIRECTORY_SEPARATOR . 'certificates' . DIRECTORY_SEPARATOR . $name))) {
                                $path_smart = storage_path('smart' . DIRECTORY_SEPARATOR . 'certificate_smart.pem');
                                if (file_exists($path_smart)) {
                                    $pem = file_get_contents($path_smart);
                                    file_put_contents(storage_path('app' . DIRECTORY_SEPARATOR . 'certificates' . DIRECTORY_SEPARATOR . $name), $pem);
                                } else {
                                    return [
                                        'success' => false,
                                        'message' => 'No se pudo obtener el certificado smart'
                                    ];
                                }
                            }
                            CacheTrait::clearCache('vc_company');
                            $company->save();
                        }
                    }
                }
            }
            return [
                'success' => true,
                'message' => 'Datos guardados correctamente'
            ];
        } else {
            $company = Company::active();
            $number = $company->number;
            $pse_url = $company->pse_url;
            $pse_token = $company->pse_token;
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $pse_token,
                'Ruc' => $company->number
            ])->post($pse_url . '/api/pse/get_variables/sub_client', [
                'number' => $number,
            ]);

            $response = $response->json();

            if ($response['success']) {
                $pse_url = $response['pse_url'];
                $pse_username = $response['pse_username'];
                $pse_password = $response['pse_password'];
                $send_for_sunat = $response['send_for_sunat'];
                $name = 'certificate_smart.pem';
                if ($pse_url && $pse_username && $pse_password) {
                    $company->soap_url = $pse_url;
                    $company->soap_username = $pse_username;
                    $company->soap_password = $pse_password;
                    $company->soap_send_id = $send_for_sunat ? '01' : '02';
                    $company->certificate = $name;
                    if (!file_exists(storage_path('app' . DIRECTORY_SEPARATOR . 'certificates'))) {
                        mkdir(storage_path('app' . DIRECTORY_SEPARATOR . 'certificates'));
                    }

                    if (!file_exists(storage_path('app' . DIRECTORY_SEPARATOR . 'certificates' . DIRECTORY_SEPARATOR . $name))) {
                        $path_smart = storage_path('smart' . DIRECTORY_SEPARATOR . 'certificate_smart.pem');
                        if (file_exists($path_smart)) {
                            $pem = file_get_contents($path_smart);
                            file_put_contents(storage_path('app' . DIRECTORY_SEPARATOR . 'certificates' . DIRECTORY_SEPARATOR . $name), $pem);
                        } else {
                            return [
                                'success' => false,
                                'message' => 'No se pudo obtener el certificado smart'
                            ];
                        }
                    }
                    CacheTrait::clearCache('vc_company');

                    $company->save();
                    return [
                        'success' => true,
                    ];
                }
                return [
                    'success' => false,
                    'message' => 'No se pudo obtener los datos de PSE'
                ];
            }
        }
    }
    public function create()
    {
        return view('tenant.companies.form');
    }

    public function tables()
    {
        $soap_sends = config('tables.system.soap_sends');
        $soap_types = SoapType::all();
        $userType = auth()->user()->type;

        return compact('soap_types', 'soap_sends', 'userType');
    }

    public function record()
    {
        $company = Company::active();
        $record = new CompanyResource($company);

        return $record;
    }

    public function store(CompanyRequest $request)
    {
        CacheTrait::clearCache('vc_company');
        $id = $request->input('id');
        $company = Company::find($id);
        $company->type_send_pse = $request->type_send_pse ? 1 : 2;
        $company->fill($request->all());
        $company->save();

        if ($company->pse && $company->pse_token && $company->pse_url) {
            // $company->soap_send_id = '02';
            $company->soap_type_id = '02';
            $company->save();
        }

        DocumentType::where('id', '01')->update(['active' => !$company->is_rus]);
        return [
            'success' => true,
            'message' => 'Empresa actualizada'
        ];
    }

    public function uploadFile(Request $request)
    {

        if ($request->hasFile('file')) {

            $company = Company::active();

            $type = $request->input('type');

            $file = $request->file('file');

            $ext = $file->getClientOriginalExtension();
            $name = $type . '_' . $company->number . '.' . $ext;


            if (($type === 'logo')) {
                $v = request()->validate(['file' => 'required|mimes:jpeg,png,jpg,gif,svg|max:2048']);
                UploadFileHelper::checkIfValidFile($name, $file->getPathName(), true);
                $path = $type === 'logo' ? 'app/public/uploads/logos' : 'app/certificates';
                $request->file->move(storage_path($path), $name);
            }
            if (($type === 'bg_default')) {
                $v = request()->validate(['file' => 'required|mimes:jpeg,png,jpg,gif,svg|max:2048']);
                UploadFileHelper::checkIfValidFile($name, $file->getPathName(), true);
                $path = $type === 'bg_default' ? 'app/public/uploads/logos' : 'app/certificates';
                $request->file->move(storage_path($path), $name);
            }


            if (($type === 'favicon')) {
                request()->validate(['file' => 'required|image|mimes:png|max:1024']);
                // $request->file->move(storage_path('app/public/uploads/favicons'), $name);
                UploadFileHelper::checkIfValidFile($name, $file->getPathName(), true);
                $path = 'app/public/uploads/favicons';
                $request->file->move(storage_path($path), $name);
            }

            if (($type === 'app_logo')) {
                request()->validate(['file' => 'required|mimes:jpeg,png,jpg,gif,svg|max:2048']);
                UploadFileHelper::checkIfValidFile($name, $file->getPathName(), true);
                $request->file->move(storage_path('app/public/uploads/logos'), $name);
                //$file->storeAs('public/uploads/logos', $name);
            }
            if (($type === 'footer_logo')) {
                request()->validate(['file' => 'required|mimes:jpeg,png,jpg,gif,svg|max:2048']);
                UploadFileHelper::checkIfValidFile($name, $file->getPathName(), true);
                $request->file->move(storage_path('app/public/uploads/logos'), $name);
                //$file->storeAs('public/uploads/logos', $name);
            }

            if (($type === 'img_firm')) {
                request()->validate(['file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048']);
                UploadFileHelper::checkIfValidFile($name, $file->getPathName(), true);
                //  $file->storeAs('public/uploads/firms', $name);
                $request->file->move(storage_path('app/public/uploads/firms'), $name);
            }



            $company->$type = $name;

            $company->save();
            CacheTrait::clearCache('vc_company');

            return [
                'success' => true,
                'message' => __('app.actions.upload.success'),
                'name' => $name,
                'type' => $type
            ];
        }
        return [
            'success' => false,
            'message' =>  __('app.actions.upload.error'),
        ];
    }


    /**
     * Registrar datos de configuracion para enviar xml/cdr a PSE
     *
     * @param  Request $request
     * @return array
     */
    public function storeSendPse(CompanyPseRequest $request)
    {
        $company = Company::firstOrFail();
        $company->send_document_to_pse = $request->send_document_to_pse;
        $company->url_signature_pse = $request->url_signature_pse;
        $company->url_send_cdr_pse = $request->url_send_cdr_pse;
        $company->client_id_pse = $request->client_id_pse;
        $company->url_login_pse = $request->url_login_pse;
        $company->user_pse = $request->user_pse;
        $company->password_pse = $request->password_pse ?? $company->password_pse;
        $company->save();
        CacheTrait::clearCache('vc_company');

        return [
            'success' => true,
            'message' => 'Datos guardados correctamente'
        ];
    }


    /**
     * Obtener datos de configuracion de PSE
     *
     * @param  Request $request
     * @return array
     */
    public function recordSendPse()
    {

        $company = Company::firstOrFail();

        return [
            'send_document_to_pse' => $company->send_document_to_pse,
            'url_signature_pse' => $company->url_signature_pse,
            'url_send_cdr_pse' => $company->url_send_cdr_pse,
            'client_id_pse' => $company->client_id_pse,
            'url_login_pse' => $company->url_login_pse,
            'user_pse' => $company->user_pse,
            'pse' => $company->pse,
            // 'password_pse' => $company->password_pse,
        ];
    }


    /**
     * Registrar datos de configuracion para WhatsApp Api
     *
     * @param  CompanyWhatsAppApiRequest $request
     * @return array
     */
    public function storeWhatsAppApi(Request $request)
    {
        $company = Company::active();
        //  $company->ws_api_token = $request->ws_api_token;
        $company->ws_api_phone_number_id = $request->ws_api_phone_number_id;
        $company->gekawa_1 = $request->gekawa_1;
        $company->gekawa_url = $request->gekawa_url;
        $company->gekawa_2 = $request->gekawa_2;
        $company->save();
        CacheTrait::clearCache('vc_company');

        return [
            'success' => true,
            'message' => 'Datos guardados correctamente'
        ];
    }


    /**
     * 
     * Obtener datos de configuracion de WhatsApp Api
     *
     * @param  Request $request
     * @return array
     */
    public function recordWhatsAppApi()
    {
        $company = Company::selectDataWhatsAppApi()->firstOrFail();



        return [
            'gekawa_1' => $company->gekawa_1,
            'gekawa_2' => $company->gekawa_2,
            'gekawa_url' => $company->gekawa_url,
            'ws_api_token' => $company->ws_api_token,
            'ws_api_phone_number_id' => $company->ws_api_phone_number_id,
            ''
        ];
    }
}

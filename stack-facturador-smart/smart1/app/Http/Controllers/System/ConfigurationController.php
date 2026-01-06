<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Resources\System\DigemidCollection;
use App\Imports\DigemidImport;
use App\Imports\DigemidImportDelete;
use Exception;
use Illuminate\Http\Request;
use App\Models\System\Configuration;
use Illuminate\Support\Facades\DB;
use App\Models\System\Client;
use Hyn\Tenancy\Environment;
use Modules\Finance\Helpers\UploadFileHelper;
use Maatwebsite\Excel\Excel;
use App\Models\System\Digemid;
use Illuminate\Support\Facades\Storage;

class ConfigurationController extends Controller
{

    public function customTenant()
    {
        return view('system.configuration.custom-tenant');
    }

    public function index()
    {
        $configuration = Configuration::first();
        $api_whatsapp = env('API_WHATSAPP');
        $name = env('APP_URL_BASE');

        return view('system.configuration.index', compact('configuration', 'api_whatsapp', 'name'));
    }

    public function record()
    {
        $configuration = Configuration::first();
        return [
            'configuration'        => $configuration,
            'token_public_culqui'  => $configuration->token_public_culqui,
            'token_private_culqui' => $configuration->token_private_culqui,
            'gekawa1'              => $configuration->gekawa1,
            'gekawa2'              => $configuration->gekawa2,
            'gekawaurl'            => $configuration->gekawaurl,
            'tenant_logo'          => url($configuration->tenant_logo),
            'tenant_bg_logo'       => url($configuration->tenant_bg_logo),
            'tenant_favicon'       => url($configuration->tenant_favicon),
        ];
    }

    public function updateField(Request $request)
    {
        $configuration = Configuration::first();
        $field = $request->field;
        $value = $request->value;
        $configuration->$field = $value;
        $configuration->save();
        return [
            'success' => true,
            'message' => 'Configuración actualizada',
        ];
    }


    public function store(Request $request)
    {
        $configuration = Configuration::first();

        if ($request->token_public_culqui) {
            $configuration->token_public_culqui = $request->token_public_culqui;
        }

        if ($request->token_private_culqui) {
            $configuration->token_private_culqui = $request->token_private_culqui;
        }

        if ($request->url_apiruc) {
            $configuration->url_apiruc = $request->url_apiruc;
        }

        if ($request->token_apiruc) {
            $configuration->token_apiruc = $request->token_apiruc;
        }
        if ($request->whatsapp) {
            $configuration->whatsapp = $request->whatsapp;
        }

            $configuration->apk_url = $request->apk_url;
            $this->updateApkUrl($request->apk_url);


        if ($request->gekawa1) {
            $configuration->gekawa1 = $request->gekawa1;
        }

        if ($request->gekawa2) {
            $configuration->gekawa2 = $request->gekawa2;
        }


        if ($request->url_search_documents) {
            $configuration->url_search_documents = $request->url_search_documents;
        }

        if ($request->gekawaurl) {
            $configuration->gekawaurl = $request->gekawaurl;
        }

        $configuration->save();

        return [
            'success' => true,
            'message' => 'Datos guardados con éxito'
        ];
    }


    public function apiruc()
    {

        $configuration = Configuration::first();

        return [
            'url_apiruc' => $configuration->url_apiruc,
            'token_apiruc' => $configuration->token_apiruc,
        ];
    }

    public function storeLoginSettings()
    {
        request()->validate([
            'position_form' => 'required|in:left,right',
            'show_logo_in_form' => 'required|boolean',
            'position_logo' => 'required|in:top-left,top-right,bottom-left,bottom-right,none',
            'show_socials' => 'required|boolean',
            'use_login_global' => 'required|boolean',
        ]);

        $config = Configuration::first();
        $loginConfig = $config->login;
        foreach (request()->all() as $key => $option) {
            $loginConfig->$key = $option;
        }

        $config->login = $loginConfig;
        $config->use_login_global = request('use_login_global');
        $config->save();

        return response()->json([
            'success' => true,
            'message' => 'Información actualizada.',
        ], 200);
    }
    public function bg_imagen(Request $request)
    {

        if ($request->hasFile('file')) {
            $configuration = Configuration::first();
            $type = $request->input('type');
            $file = $request->file('file');
            $ext = $file->getClientOriginalExtension();
            $name = $type . '.' . $ext;
            if (($type === 'logo_admin')) {
                $v = request()->validate(['file' => 'required|mimes:jpeg,png,jpg,gif,svg|max:2048']);
                UploadFileHelper::checkIfValidFile($name, $file->getPathName(), true);
                $path = $type === 'logo_admin' ? 'app/public/uploads/logos' : 'app/certificates';
                $request->file->move(storage_path($path), $name);
                $configuration->logo = $name;
                $configuration->save();
            }
            if ($type === 'bg_default_admin') {
                $path =  'bg_default_admin' ? 'app/public/uploads/logos' : 'app/certificates';

                UploadFileHelper::checkIfValidFile($name, $file->getPathName(), true);
                $request->file->move(storage_path($path), $name);
                $configuration->bg_image = $name;
                $configuration->save();
            }

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
    public function storeBgLogin()
    {
        request()->validate([
            'image' => 'required|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        $config = Configuration::first();
        if (request()->hasFile('image') && request()->file('image')->isValid()) {
            $file = request()->file('image');
            $ext = $file->getClientOriginalExtension();
            $name = time() . '.' . $ext;
            $path = 'public/uploads/login';

            UploadFileHelper::checkIfValidFile($name, $file->getPathName(), true);

            $file->storeAs($path, $name);

            $loginConfig = $config->login;
            $basePathStorage = 'storage/uploads/login/';
            if (request('type') === 'bg') {
                $loginConfig->type = 'image';
                $loginConfig->image = asset($basePathStorage . $name);
            } else {
                $loginConfig->logo = asset($basePathStorage . $name);
            }
            $config->login = $loginConfig;
            $config->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Información actualizada.',
        ], 200);
    }

    public function InfoIndex()
    {
        $memory_limit = ini_get('memory_limit');
        $memory_in_byte = number_format(self::return_bytes($memory_limit), '2', ',', '.');
        $pcre_backtrack_limit = ini_get('pcre.backtrack_limit');
        $all_config = [
            'max_execution_time' => ini_get('max_execution_time'),
            'max_input_time' => ini_get('max_input_time'),
            'post_max_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'request_terminate_timeout' => ini_get('request_terminate_timeout'),
            'date_timezone' => ini_get('date.timezone'),
            'version_laravel' => app()->version(),
        ];

        return view('system.configuration.info', compact(
            'memory_limit',
            'memory_in_byte',
            'pcre_backtrack_limit',
            'all_config'
        ));
    }

    private  static function return_bytes($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        $val = substr($val, 0, -1);
        switch ($last) {
                // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $val;
    }

    public function apkurl()
    {

        $configuration = Configuration::first();

        return [
            'apk_url' => $configuration->apk_url
        ];
    }

    public function updateApkUrl($apk_url)
    {
        DB::connection('system')->transaction(function () use ($apk_url) {

            $records = Client::get();

            foreach ($records as $row) {

                $tenancy = app(Environment::class);
                $tenancy->tenant($row->hostname->website);

                DB::connection('tenant')->table('configurations')->where('id', 1)->update(['apk_url' => $apk_url]);
            }
        });
    }


    /**
     * 
     * Actualizar el descuento global a 02 (Afecta la base) en todos los clientes
     *
     * @return array
     */
    public function updateTenantDiscountTypeBase()
    {

        DB::connection('system')->transaction(function () {

            $records = Client::get();

            foreach ($records as $row) {
                $tenancy = app(Environment::class);
                $tenancy->tenant($row->hostname->website);

                DB::connection('tenant')->table('configurations')->where('id', 1)->update(['global_discount_type_id' => '02']);
            }
        });

        return [
            'success' => true,
            'message' => 'El proceso se realizó correctamente'
        ];
    }


    /**
     *
     * @param  Request $request
     * @return array
     */
    public function storeOtherConfiguration(Request $request)
    {
        $record = Configuration::first();
        $record->regex_password_client = $request->regex_password_client;
        $record->minutes_send_regularizare = $request->minutes_send_regularizare;
        $record->minutes_verify_cdr = $request->minutes_verify_cdr;
        $record->max_attempt_pse = $request->max_attempt_pse;
        $record->minute_to_validate = $request->minute_to_validate;
        $record->save();


        return [
            'success' => true,
            'message' => 'Configuración actualizada',
        ];
    }

    public function getOtherConfiguration()
    {
        $record = Configuration::first();
        return [
            'regex_password_client' => $record->regex_password_client,
            'minutes_send_regularizare' => $record->minutes_send_regularizare,
            'minutes_verify_cdr' => $record->minutes_verify_cdr,
            'max_attempt_pse' => $record->max_attempt_pse,
            'minute_to_validate' => $record->minute_to_validate,
        ];
    }
    public function update_digemid(Request $request)

    {
        if ($request->hasFile('file')) {
            try {
                $import = new DigemidImport();
                $import->import($request->file('file'), null, Excel::XLSX);
                $updated  = $import->getData();
                return [
                    'success' => true,
                    'message' =>  "El archivo fue cargado correctamente con " . $updated['registered'] . " registros",
                    'data' => $updated
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' =>  $e->getMessage()
                ];
            }
        }
    }
    /**
     * Obtener listado de registros DIGEMID
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function records_digemid(Request $request)
    {
            $records = Digemid::query();

            $column = $request->column;
            $value = $request->value;

            if ($column && $value) {
                $records->where($column, 'like', '%' . $value . '%');
            }
            return new DigemidCollection($records->paginate(20));
    }

    /**
     * Obtener un registro específico de DIGEMID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function record_digemid($cod_prod)
    {
        try {
            $record = Digemid::where('cod_prod', $cod_prod)->first();

            return [
                'success' => true,
                'data' => $record
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Eliminar un registro específico de DIGEMID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function remove_digemid($cod_prod)
    {
        try {
            
            Digemid::where('cod_prod', $cod_prod)->delete();
            

            return [
                'success' => true,
                'message' => 'Registro eliminado con éxito'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Insertar un nuevo registro DIGEMID
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function insert_digemid(Request $request)
    {
        try {
            $record = new Digemid($request->all());
            $record->save();

            return [
                'success' => true,
                'message' => 'Registro creado con éxito',
                'data' => $record
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Eliminar múltiples registros DIGEMID
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete_digemid(Request $request)
    {
        $import = new DigemidImportDelete();
        $import->import($request->file('file'), null, Excel::XLSX);
        $updated  = $import->getData();
        return [
            'success' => true,
            'message' =>  "El archivo fue cargado correctamente con " . $updated['registered'] . " registros",
            'data' => $updated
        ];
    }

    public function uploadDefaultImage(Request $request){
        try {
            if (!$request->hasFile('file')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se ha enviado ningún archivo'
                ], 400);
            }

            $type = $request->input('type');
            $file = $request->file('file');

            // Validar el tipo de archivo
            $request->validate([
                'file' => 'required|mimes:jpeg,jpg,png|max:2048',
                'type' => 'required|in:logo,background,favicon'
            ]);

            // Verificar que el archivo sea válido
            UploadFileHelper::checkIfValidFile($file->getClientOriginalName(), $file->getPathName(), true);

            $ext = $file->getClientOriginalExtension();
            $filename ='tenant_'. $type . '_' . time() . '.' . $ext;
            
            // Definir la ruta de almacenamiento según el tipo
            $storagePath = 'public/uploads/' . ($type === 'favicon' ? 'favicons' : 'logos');
            $publicPath = 'storage/uploads/' . ($type === 'favicon' ? 'favicons' : 'logos') . '/' . $filename;

            // Crear el directorio si no existe
            if (!Storage::exists($storagePath)) {
                Storage::makeDirectory($storagePath);
            }

            // Guardar el archivo
            $file->storeAs($storagePath, $filename);

            // Actualizar la configuración según el tipo
            $configuration = Configuration::first();
            
            switch ($type) {
                case 'logo':
                    $configuration->tenant_logo = $publicPath;
                    break;
                case 'background':
                    $configuration->tenant_bg_logo = $publicPath;
                    break;
                case 'favicon':
                    $configuration->tenant_favicon = $publicPath;
                    break;
            }

            $configuration->save();

            return response()->json([
                'success' => true,
                'message' => 'Imagen subida exitosamente',
                'data' => [
                    'filename' => $filename,
                    'path' => $publicPath,
                    'type' => $type
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir la imagen: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteDefaultImage($type)
    {
        try {
            // Validar el tipo
            if (!in_array($type, ['logo', 'background', 'favicon'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de imagen no válido'
                ], 400);
            }

            $configuration = Configuration::first();
            $fieldName = '';
            $filePath = '';

            // Determinar el campo y ruta del archivo según el tipo
            switch ($type) {
                case 'logo':
                    $fieldName = 'tenant_logo';
                    $filePath = $configuration->tenant_logo;
                    break;
                case 'background':
                    $fieldName = 'tenant_bg_logo';
                    $filePath = $configuration->tenant_bg_logo;
                    break;
                case 'favicon':
                    $fieldName = 'tenant_favicon';
                    $filePath = $configuration->tenant_favicon;
                    break;
            }

            // Si no hay archivo guardado, retornar error
            if (!$filePath) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay imagen para eliminar'
                ], 404);
            }

            // Eliminar el archivo físico si existe
            $fullPath = public_path($filePath);
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            // Limpiar el campo en la base de datos
            $configuration->$fieldName = null;
            $configuration->save();

            return response()->json([
                'success' => true,
                'message' => 'Imagen eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la imagen: ' . $e->getMessage()
            ], 500);
        }
    }
}

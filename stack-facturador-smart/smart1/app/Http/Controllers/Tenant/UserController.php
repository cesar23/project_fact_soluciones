<?php

namespace App\Http\Controllers\Tenant;

use App\Services\ApiEncryption;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UserRequest;
use App\Http\Resources\Tenant\UserCollection;
use App\Http\Resources\Tenant\UserResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\System\Configuration as SystemConfiguration;
use App\Models\Tenant\Cash;
use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Module;
use App\Models\Tenant\Series;
use App\Models\Tenant\User;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Zone;
use App\Models\Tenant\Catalogs\IdentityDocumentType;
use App\Models\Tenant\CustomInitRoute;
use App\Models\Tenant\FilterItemByUser;
use App\Models\Tenant\Person;
use App\Models\Tenant\Warehouse;
use App\Traits\CacheTrait;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Finance\Helpers\UploadFileHelper;
use Modules\BusinessTurn\Models\BusinessTurn;

class UserController extends Controller
{
    use CacheTrait;
    // Agregar estos métodos en la clase UserController

public function getUserFiltersSetItems()
{
    $user = User::findOrFail(auth()->user()->id);
    $filter = FilterItemByUser::where('user_id', $user->id)->first();
    return response()->json($filter);
}
public function logoutUser(Request $request)
{
    $user_id = $request->input('user_id');
    $user = User::find($user_id);
    
    if ($user) {
        $user->force_logout_at = now();
        $user->save();
        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }
    
    return response()->json([
        'success' => false,
        'message' => 'Usuario no encontrado'
    ], 404);
}

/**
 * Forzar cierre de sesión de un usuario específico
 */
private function forceLogoutUser(User $user)
{
    try {
        // Invalidar todos los tokens del usuario
        $user->api_token = null;
        $user->remember_token = null;
        $user->save();

        // Si el usuario está actualmente autenticado, cerrar su sesión
        if (auth()->check() && auth()->id() == $user->id) {
            auth()->logout();
        }

        // Limpiar sesión de la base de datos si es necesario
        $this->clearUserSessionFromDatabase($user);

        // Limpiar cache
        $this->clearUserCache($user);

    } catch (\Exception $e) {
        Log::error('Error al cerrar sesión del usuario: ' . $e->getMessage());
    }
}

/**
 * Limpiar sesión del usuario desde la base de datos
 */
private function clearUserSessionFromDatabase(User $user)
{
    try {
        // Si usas sesiones en base de datos
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->delete();
    } catch (\Exception $e) {
        // Ignorar si no existe la tabla sessions
    }
}

/**
 * Limpiar cache del usuario
 */
private function clearUserCache(User $user)
{
    try {
        // Limpiar cache específico del usuario
        $cacheKeys = [
            'user_session_' . $user->id,
            'user_data_' . $user->id,
            'user_permissions_' . $user->id
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    } catch (\Exception $e) {
        // Ignorar errores de cache
    }
}
public function changeUserFiltersSetItems(Request $request) 
{
    $user = User::findOrFail(auth()->user()->id);
    $filter = FilterItemByUser::where('user_id', $user->id)->first();
    
    if (!$filter) {
        $filter = new FilterItemByUser();
        $filter->user_id = $user->id;
    }
    
    $filter->filter_active = $request->filter_active;
    $filter->save();

    return [
        'success' => true,
        'message' => 'Configuración actualizada'
    ];
}

public function getUsersOpenCash(){
    $cashes = Cash::where('state', true)->get()->transform(function ($row) {
        return [
            'id' => $row->id,
            'name' => $row->user->name . " - " . $row->reference_number
        ];
    });
    return response()->json($cashes);
}

public function changeUserFiltersSetItemsType(Request $request)
{
    $user = User::findOrFail(auth()->user()->id);
    $filter = FilterItemByUser::where('user_id', $user->id)->first();
    
    if (!$filter) {
        $filter = new FilterItemByUser();
        $filter->user_id = $user->id;
    }
    
    $filter->filter_name = $request->filter_name == 1 ? 'pack' : 'individual';
    $filter->save();

    return [
        'success' => true,
        'message' => 'Configuración actualizada'
    ];
}
public function changeCash(Request $request){
    $cash_id = $request->input('cash_id');
    $user_id = Cash::findOrFail($cash_id)->user_id;
    /** @var User $user */
    $user = auth()->user();
    $user->user_cash_id = $user_id;
    $user->save();
    return [
        'success' => true,
        'message' => 'Caja actualizada'
    ];
}
    public function getCustomers()
    {
        $customers = Person::whereType('customers')->limit(20)->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => $row->number . ' - ' . $row->name,
                'name' => $row->name,
                'number' => $row->number,
                'identity_document_type_id' => $row->identity_document_type_id,
                'identity_document_type_code' => $row->identity_document_type->code,
                'addresses' => $row->addresses,
                'address' =>  $row->address
            ];
        });

        return response()->json($customers);
    }
    public function usersByWarehouse($warehouse_id){
        $warehouse = Warehouse::find($warehouse_id);
        $users = User::where('establishment_id', $warehouse->establishment_id)->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'name' => $row->name
            ];
        });
        return response()->json($users);
    }
    public function changeEstablishment(Request $request)
    {
        try {
            $user_id = auth()->user()->id;
            $establishment_id = $request->input('establishment_id');
            if ($establishment_id == null) {
                return [
                    'success' => false,
                    'message' => 'No se puede cambiar el establecimiento'
                ];
            }
            $establishment_exists = Establishment::find($establishment_id);
            if (!$establishment_exists) {
                return [
                    'success' => false,
                    'message' => 'No se puede cambiar el establecimiento'
                ];
            }
            $user = User::findOrFail($user_id);
            $user->establishment_id = $establishment_id;
            $user->save();
            CacheTrait::clearCache('series_by_user_id_' . $user->id);
            return [
                'success' => true,
                'message' => 'Establecimiento actualizado'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'No se puede cambiar el establecimiento'
            ];
        }
    }
    public function changePassword(Request $request)
    {
        $user_id = $request->input('client_id');
        $password = $request->input('password');
        $password_confirmation = $request->input('password_confirmation');
        if ($password != $password_confirmation) {
            return [
                'success' => false,
                'message' => 'Las contraseñas no coinciden'
            ];
        }
        $user = User::findOrFail($user_id);
        $user->password = bcrypt($password);
        $user->save();
        return [
            'success' => true,
            'message' => 'Contraseña actualizada'
        ];
    }
    public function unlock(Request $request)
    {
        $user_id = $request->input('user_id');
        $user = User::findOrFail($user_id);
        $user->is_locked = false;
        $user->msg_locked = null;
        $user->save();
        return [
            'success' => true,
            'message' => 'Usuario desbloqueado'
        ];
    }
    public function lock(Request $request)
    {
        $user_id = $request->input('user_id');
        $message = $request->input('message');
        $message = trim($message);
        $user = User::findOrFail($user_id);
        $user->is_locked = true;
        if ($message == null || $message == "" || $message == " ") {
            $message = "Su cuenta ha sido bloqueada. Comuníquese con el administrador";
        }
        $user->msg_locked = $message;
        $user->save();
        return [
            'success' => true,
            'message' => 'Usuario bloqueado'
        ];
    }
    public function cambiarContrasena(Request $request)
    {
        $url = explode('.', $request->getHttpHost());
        $part = $url[0];
        $password = $request->input('password');
        $password_confirmation = $request->input('password_confirmation');


        if ($password != $password_confirmation) {
            return [
                'success' => false,
                'message' => 'Las contraseñas no coinciden'
            ];
        }
        if ($part == "demo") {
            $password = "123456";
        }
        $user_id = auth()->user()->id;
        $user = User::findOrFail($user_id);
        $user->password = bcrypt($password);
        $user->save();
        auth()->logout();
        return redirect()->guest('login');
    }
    public function index()
    {
        return view('tenant.users.index');
    }

    public function record($id)
    {
        $word = "xddddddfasfsdfsd|Luna";
        // $encrypted = (new ApiEncryption())->encrypt($word);
        $user = User::findOrFail($id);


        $record = new UserResource($user);

        return $record;
    }
    public function downloadQrStore()
    {
        // URL de la app en Play Store
        $play_store_url = "https://play.google.com/store/apps/details?id=com.peru.app";
        $configuration = SystemConfiguration::first();
        $apk_url = $configuration->apk_url;
        if ($apk_url) {
            $play_store_url = $apk_url;
        }
    
        // Obtener datos del dominio
        $url = url('');
        $domain = url('/');
        $url_parts = explode('.', $domain);
        array_shift($url_parts);
        $domain = implode('.', $url_parts);
    
        // Crear generador de QR
        $qrCodeGenerate = new \App\CoreFacturalo\Helpers\QrCode\QrCodeGenerate();
    
        // Generar solo el QR de descarga de app
        $qrBase64 = $qrCodeGenerate->displayPNGBase64($play_store_url, 300);
        $qrImage = imagecreatefromstring(base64_decode($qrBase64));
    
        // Crear una imagen combinada (más pequeña ya que solo tenemos 1 QR)
        $width = 350;
        $height = 400; // Altura reducida ya que solo tenemos un QR
        $combinedImage = imagecreatetruecolor($width, $height);
    
        // Fondo blanco
        $white = imagecolorallocate($combinedImage, 255, 255, 255);
        imagefill($combinedImage, 0, 0, $white);
    
        // Añadir texto descriptivo
        $black = imagecolorallocate($combinedImage, 0, 0, 0);
        $font = 5; // Fuente predeterminada
    
        // Primero dibujar el QR centrado
        imagecopy($combinedImage, $qrImage, 30, 75, 0, 0, 300, 300);
    
        // Texto para el QR
        
        // Cargar el logo y prepararlo
        $logo = imagecreatefrompng(public_path('images/fondos/smart-fondo-transparente.png'));
        $logo_width = imagesx($logo);
        $logo_height = imagesy($logo);
    
        // Reducir el tamaño del logo
        $new_logo_width = $logo_width * 0.3;
        $new_logo_height = $logo_height * 0.3;
    
        // Crear una imagen temporal para el logo redimensionado
        $resized_logo = imagecreatetruecolor($new_logo_width, $new_logo_height);
    
        // Preservar transparencia
        imagealphablending($resized_logo, false);
        imagesavealpha($resized_logo, true);
        $transparent = imagecolorallocatealpha($resized_logo, 255, 255, 255, 127);
        imagefilledrectangle($resized_logo, 0, 0, $new_logo_width, $new_logo_height, $transparent);
    
        // Redimensionar el logo
        imagecopyresampled($resized_logo, $logo, 0, 0, 0, 0, $new_logo_width, $new_logo_height, $logo_width, $logo_height);
    
        // Dibujar el logo en la parte superior (encima del QR)
        imagecopy($combinedImage, $resized_logo, ($width - $new_logo_width) / 2, 5, 0, 0, $new_logo_width, $new_logo_height);
    
        // Liberar memoria
        imagedestroy($logo);
        imagedestroy($resized_logo);
    
        // Convertir la imagen combinada a una cadena PNG
        ob_start();
        imagepng($combinedImage);
        $combinedImageString = ob_get_clean();
    
        // Liberar memoria
        imagedestroy($qrImage);
        imagedestroy($combinedImage);
    
        return response($combinedImageString)
            ->header('Content-Type', 'image/png');
    }
    public function downloadQrVirtualStore($id = null)
    {
        if ($id == null) {
            // Si no hay ID, mostrar solo el QR de la tienda (sin QR de usuario)
            return $this->downloadQrStore();
        }
        $user = User::findOrFail($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Logueate como cliente para descargar el QR'
            ], 400);
        }
        if ($user->type != 'client') {
            return $this->downloadQrStore();
        }
        $play_store_url = "https://play.google.com/store/apps/details?id=com.peru.app";
        $configuration = SystemConfiguration::first();
        $apk_url = $configuration->apk_url;
        if ($apk_url) {
            $play_store_url = $apk_url;
        }
        $type_user = $user->type;
        $email = $user->email;
        $api_key = $user->api_token;
        $url = url('');
        $domain = url('/');
        $url_parts = explode('.', $domain);
        array_shift($url_parts);
        $domain = implode('.', $url_parts);
        if (!$api_key) {
            $user->api_token = str_random(50);
            $user->save();
            $api_key = $user->api_token;
        }

        // QR 1: Datos encriptados
        $to_encrypt = $url . ":::" . $type_user . ":::" . $email;
        $encrypted = (new ApiEncryption())->encrypt($to_encrypt);

        // Usar QrCodeGenerate en lugar de Endroid
        $qrCodeGenerate = new \App\CoreFacturalo\Helpers\QrCode\QrCodeGenerate();

        // Generar QR1 (datos de acceso)
        $qr1Base64 = $qrCodeGenerate->displayPNGBase64($encrypted, 300);
        $qr1Image = imagecreatefromstring(base64_decode($qr1Base64));

        // Generar QR2 (descarga de app)
        $qr2Base64 = $qrCodeGenerate->displayPNGBase64($play_store_url, 300);
        $qr2Image = imagecreatefromstring(base64_decode($qr2Base64));

        // Crear una imagen combinada
        $width = 650;
        $height = 700;
        $combinedImage = imagecreatetruecolor($width, $height);

        // Fondo blanco
        $white = imagecolorallocate($combinedImage, 255, 255, 255);
        imagefill($combinedImage, 0, 0, $white);

        // Añadir texto descriptivo
        $black = imagecolorallocate($combinedImage, 0, 0, 0);
        $font = 5; // Fuente predeterminada

        // Primero cargar el logo y prepararlo, pero no dibujarlo aún
        $logo = imagecreatefrompng(public_path('images/fondos/smart-fondo-transparente.png'));
        $logo_width = imagesx($logo);
        $logo_height = imagesy($logo);

        // Reducir el tamaño del logo
        $new_logo_width = $logo_width * 0.3;
        $new_logo_height = $logo_height * 0.3;

        // Crear una imagen temporal para el logo redimensionado
        $resized_logo = imagecreatetruecolor($new_logo_width, $new_logo_height);

        // Preservar transparencia
        imagealphablending($resized_logo, false);
        imagesavealpha($resized_logo, true);
        $transparent = imagecolorallocatealpha($resized_logo, 255, 255, 255, 127);
        imagefilledrectangle($resized_logo, 0, 0, $new_logo_width, $new_logo_height, $transparent);

        // Redimensionar el logo
        imagecopyresampled($resized_logo, $logo, 0, 0, 0, 0, $new_logo_width, $new_logo_height, $logo_width, $logo_height);

        // Dibujar primero los QR
        imagecopy($combinedImage, $qr2Image, 175, 70, 0, 0, 300, 300);

        // Texto para QR1
        imagestring($combinedImage, $font, 250, 380, "ACCEDE A TU CUENTA", $black);
        imagestring($combinedImage, 4, 225, 395, "Escanea el qr desde la app", $black);

        // Copiar QR1
        imagecopy($combinedImage, $qr1Image, 175, 420, 0, 0, 300, 300);

        // Finalmente, dibujar el logo (para que quede encima)
        imagecopy($combinedImage, $resized_logo, ($width - $new_logo_width) / 2, 5, 0, 0, $new_logo_width, $new_logo_height);

        // Liberar memoria
        imagedestroy($logo);
        imagedestroy($resized_logo);

        // Convertir la imagen combinada a una cadena PNG
        ob_start();
        imagepng($combinedImage);
        $combinedImageString = ob_get_clean();

        // Liberar memoria
        imagedestroy($qr1Image);
        imagedestroy($qr2Image);
        imagedestroy($combinedImage);

        return response($combinedImageString)
            ->header('Content-Type', 'image/png');
    }

    public function downloadQr($id = null)
    {
        if ($id == null) {
            $id = auth()->user()->id;
        }
        $play_store_url = "https://play.google.com/store/apps/details?id=com.facturaperu.taxo";
        $configuration = SystemConfiguration::first();
        $apk_url = $configuration->apk_url;
        if ($apk_url) {
            $play_store_url = $apk_url;
        }
        $user = User::findOrFail($id);
        $type_user = $user->type;
        $email = $user->email;
        $api_key = $user->api_token;
        $url = url('');
        $domain = url('/');
        $url_parts = explode('.', $domain);
        array_shift($url_parts);
        $domain = implode('.', $url_parts);
        if (!$api_key) {
            $user->api_token = str_random(50);
            $user->save();
            $api_key = $user->api_token;
        }

        // QR 1: Datos encriptados
        $to_encrypt = $url . ":::" . $type_user . ":::" . $email;
        $encrypted = (new ApiEncryption())->encrypt($to_encrypt);

        // Usar QrCodeGenerate en lugar de Endroid
        $qrCodeGenerate = new \App\CoreFacturalo\Helpers\QrCode\QrCodeGenerate();

        // Generar QR1 (datos de acceso)
        $qr1Base64 = $qrCodeGenerate->displayPNGBase64($encrypted, 300);
        $qr1Image = imagecreatefromstring(base64_decode($qr1Base64));

        // Generar QR2 (descarga de app)
        $qr2Base64 = $qrCodeGenerate->displayPNGBase64($play_store_url, 300);
        $qr2Image = imagecreatefromstring(base64_decode($qr2Base64));

        // Crear una imagen combinada
        $width = 650;
        $height = 700;
        $combinedImage = imagecreatetruecolor($width, $height);

        // Fondo blanco
        $white = imagecolorallocate($combinedImage, 255, 255, 255);
        imagefill($combinedImage, 0, 0, $white);

        // Añadir texto descriptivo
        $black = imagecolorallocate($combinedImage, 0, 0, 0);
        $font = 5; // Fuente predeterminada

        // Título principal
        imagestring($combinedImage, $font, 250, 15, "DESCARGA LA APP", $black);
        imagecopy($combinedImage, $qr2Image, 175, 40, 0, 0, 300, 300);
        // Texto para QR1
        imagestring($combinedImage, $font, 250, 350, "ACCEDE A TU CUENTA", $black);
        imagestring($combinedImage, 4, 225, 365, "Escanea el qr desde la app", $black);

        // Copiar las imágenes QR a la imagen combinada
        imagecopy($combinedImage, $qr1Image, 175, 390, 0, 0, 300, 300);

        // Convertir la imagen combinada a una cadena PNG
        ob_start();
        imagepng($combinedImage);
        $combinedImageString = ob_get_clean();

        // Liberar memoria
        imagedestroy($qr1Image);
        imagedestroy($qr2Image);
        imagedestroy($combinedImage);

        return response($combinedImageString)
            ->header('Content-Type', 'image/png');
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

    public function tables(Request $request)
    {
        /** @var User $user */
        $user = User::find(1);
        $modulesTenant = $user->getCurrentModuleByTenant()
            ->pluck('module_id')
            ->all();

        $levelsTenant = $user->getCurrentModuleLevelByTenant()
            ->pluck('module_level_id')
            ->toArray();


        $modules = Module::with(['levels' => function ($query) use ($levelsTenant) {
            $query->whereIn('id', $levelsTenant);
        }])
            ->orderBy('order_menu')
            ->whereIn('id', $modulesTenant)
            ->get()
            ->each(function ($module) {
                return $this->prepareModules($module);
            });
        $establishments = Establishment::orderBy('description')->get();
        $documents = DocumentType::OnlyAvaibleDocuments()->get();
        $series = Series::FilterEstablishment()->FilterDocumentType()->get();
        $types = [
            ['type' => 'admin', 'description' => 'Administrador'],
            ['type' => 'seller', 'description' => 'Vendedor'],
            ['type' => 'admin_lower', 'description' => 'Usuario con restricciones'],
            ['type' => 'client', 'description' => 'Cliente'],
        ];

        $configuration = Configuration::select(['package_handlers', 'permission_to_edit_cpe', 'regex_password_user', 'items_delivery_states'])->first();
        if ($configuration->package_handlers) {
            $types = [
                ['type' => 'admin', 'description' => 'Administrador'],
                ['type' => 'seller', 'description' => 'Cajero'],
            ];
        }
        $config_permission_to_edit_cpe = $configuration->permission_to_edit_cpe;
        $config_regex_password_user = $configuration->regex_password_user;
        $zones = Zone::all();
        $routes = CustomInitRoute::all()->toArray();
        $all_modules =  Module::all()->pluck('value')->toArray();
        $supply_module = in_array('supplies', $all_modules) ? true : false;
        if($supply_module){
            $routes = array_merge($routes, [['name' => 'supplies','description' => 'Suministros', 'route' => '/supplies','id' => 35]]);
        }
        $identity_document_types = IdentityDocumentType::filterDataForPersons()->get();
        $configuration = Configuration::first()->getCollectionData();
        return compact('modules', 'routes', 'establishments', 'types', 'documents', 'series', 'config_permission_to_edit_cpe', 'zones', 'identity_document_types', 'config_regex_password_user', 'configuration');
    }

    public function regenerateToken(User $user)
    {
        $data = [
            'api_token' => $user->api_token,
            'success' => false,
            'message' => 'No puedes cambiar el token'
        ];
        if (auth()->user()->isAdmin()) {
            $user->updateToken()->push();
            $data['api_token'] = $user->api_token;
            $data['success'] = true;
            $data['message'] = 'Token cambiado';
        }
        return $data;
    }


    public function store(UserRequest $request)
    {
        // 
        $id = $request->input('id');

        if (!$id) { //VALIDAR EMAIL DISPONIBLE
            $verify = User::where('email', $request->input('email'))->first();
            if ($verify) {
                return [
                    'success' => false,
                    'message' => 'Email no disponible. Ingrese otro Email'
                ];
            }
        }

        DB::connection('tenant')->transaction(function () use ($request, $id) {
            $is_integrate_system = BusinessTurn::isIntegrateSystem();
            $integrate_user_type_id = $request->input('integrate_user_type_id');
            /** @var User $user */
            $user = User::firstOrNew(['id' => $id]);

            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->edit_delivery_state = $request->input('edit_delivery_state') ?? false;
            $user->establishment_id = $request->input('establishment_id');
            $user->type = $request->input('type');
            $user->pos_lite = $request->input('pos_lite') ?? false;
            $user->show_packers_document = $request->input('show_packers_document') ?? false;
            $user->show_dispatchers_document = $request->input('show_dispatchers_document') ?? false;
            $user->show_box = $request->input('show_box') ?? false;
            $user->show_packers_in_document = $request->input('show_packers_in_document') ?? false;
            $user->show_dispatchers_in_document = $request->input('show_dispatchers_in_document') ?? false;
            $user->show_box_in_document = $request->input('show_box_in_document') ?? false;
            $user->user_cash_id = $request->input('user_cash_id');
            $user->integrate_user_type_id = $request->input('integrate_user_type_id');
            $init_route = $request->input('init_route');
            if ($init_route == null) {
                $init_route = '/documents/create';
            }
            $user->init_route = $init_route;

            // Zona por usuario
            // $user->zone_id = $request->input('zone_id');

            if (!$id) {
                $user->api_token = str_random(50);
                $user->password = bcrypt($request->input('password'));
            } elseif ($request->has('password')) {
                if (config('tenant.password_change')) {
                    $user->password = bcrypt($request->input('password'));
                }
            }

            $user->setDocumentId($request->input('document_id'))
                ->setSeriesId($request->input('series_id'));
            $user->establishment_id = $request->input('establishment_id');

            $user->recreate_documents = $request->input('recreate_documents');
            $user->permission_edit_cpe = $request->input('permission_edit_cpe');
            $user->create_payment = $request->input('create_payment');
            $user->delete_payment = $request->input('delete_payment');
            $user->auditor = $request->input('auditor') ? $request->input('auditor') : false;

            $user->edit_purchase = $request->input('edit_purchase') ?? false;
            $user->annular_purchase = $request->input('annular_purchase') ?? false;
            $user->delete_purchase = $request->input('delete_purchase') ?? false;

            $user->edit_pos = $request->input('edit_pos') ?? false;
            $user->reopen_pos = $request->input('reopen_pos') ?? false;
            $user->delete_pos = $request->input('delete_pos') ?? false;
            $user->edit_payment = $request->input('edit_payment') ?? false;

            $user->create_order_delivery = $request->input('create_order_delivery') ?? false;
            $user->edit_order_delivery = $request->input('edit_order_delivery') ?? false;
            $user->voided_order_delivery = $request->input('voided_order_delivery') ?? false;
            $user->voided_cpe = $request->input('voided_cpe') ?? false;
            $user->note_cpe = $request->input('note_cpe') ?? false;
            $user->voided_sale_note = $request->input('voided_sale_note') ?? false;
            $user->permission_force_send_by_summary = $request->input('permission_force_send_by_summary');

            if ($user->isDirty('password')) $user->last_password_update = date('Y-m-d H:i:s');

            $this->setAdditionalData($user, $request);

            $user->save();
            if ($id == null) {
                $cash = new Cash;
                $cash->user_id = $user->id;
                $cash->date_opening = date('Y-m-d');
                $cash->time_opening = date('H:i:s');
                $cash->state = 1;
                $cash->final_balance_with_banks = 0;
                $cash->save();
            }
            $this->savePhoto($user, $request);
            $this->saveDefaultDocumentTypes($user, $request);

            if ($user->id != 1) {
                $user->setModuleAndLevelModule($request->modules, $request->levels);
            }
            CacheTrait::clearCache('series_by_user_id_' . $user->id);
            if ($is_integrate_system && $integrate_user_type_id) {
                if ($id) {
                    DB::connection('tenant')
                        ->table('module_user')
                        ->where('user_id', $user->id)
                        ->delete();

                    DB::connection('tenant')
                        ->table('module_level_user')
                        ->where('user_id', $user->id)
                        ->delete();
                }

                $user->setIntegrateUserType($integrate_user_type_id);
            }
        });

        return [
            'success' => true,
            'message' => ($id) ? 'Usuario actualizado' : 'Usuario registrado'
        ];
    }


    /**
     * 
     * Asignar datos
     *
     * @param  User $user
     * @param  UserRequest $request
     * @return void
     */
    private function setAdditionalData(User &$user, $request)
    {
        $user->edit_purchase = $request->input('edit_purchase');

        $user->identity_document_type_id = $request->identity_document_type_id;
        $user->number = $request->number;
        $user->address = $request->address;
        $user->names = $request->names;
        $user->last_names = $request->last_names;
        $user->personal_email = $request->personal_email;
        $user->corporate_email = $request->corporate_email;
        $user->personal_cell_phone = $request->personal_cell_phone;
        $user->corporate_cell_phone = $request->corporate_cell_phone;
        $user->date_of_birth = $request->date_of_birth;
        $user->contract_date = $request->contract_date;
        $user->position = $request->position;
        $user->photo_filename = $request->photo_filename;

        $user->multiple_default_document_types = $request->multiple_default_document_types;
    }


    /**
     * 
     * Guardar imágen
     *
     * @param  User $user
     * @param  UserRequest $request
     * @return void
     */
    public function savePhoto(&$user, $request)
    {
        $temp_path = $request->photo_temp_path;

        if ($temp_path) {
            $old_filename = $request->photo_filename;
            $user->photo_filename = UploadFileHelper::uploadImageFromTempFile('users', $old_filename, $temp_path, $user->id, true);
            $user->save();
        }
    }


    /**
     * 
     * Guardar documentos por defecto
     *
     * @param  User $user
     * @param  UserRequest $request
     * @return void
     */
    public function saveDefaultDocumentTypes(User $user, UserRequest $request)
    {
        $user->default_document_types()->delete();

        foreach ($request->default_document_types as $row) {
            $user->default_document_types()->create($row);
        }
    }


    public function records_lite()
    {
        $records = DB::connection('tenant')->table('users')
            ->select('id', 'name')
            ->where('type', '!=', 'integrator')
            ->where('type', '!=', 'superadmin')
            ->get()
            ->transform(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                ];
            });

        return $records;
    }
    public function records(Request $request)
    {
        $virtual_store = $request->input('virtualStore');
        $virtual_store = $virtual_store == 'true' ? true : false;
        if (!$virtual_store) {
            $records = User::where('type', '!=', 'integrator')
                ->where('type', '!=', 'superadmin')
                ->where('type', '!=', 'client');
        
        } else {
            $records = User::where('type', 'client');


        }

        if($request->input('input')){
            $records->where('name', 'like', "%{$request->input('input')}%");
        }
        $records = $records->get();

        return new UserCollection(collect($records));
    }

    public function destroy($id)
    {
        try {
            DB::connection('tenant')->beginTransaction();

            $user = User::findOrFail($id);

            // Verificar si hay caja abierta
            $cash = Cash::where('user_id', $id)
                ->where('state', true)
                ->first();

            if ($cash) {
                $cash->delete();
            }

            $user->delete();

            DB::connection('tenant')->commit();

            return [
                'success' => true,
                'message' => 'Usuario eliminado con éxito'
            ];
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();

            return [
                'success' => false,
                'message' => 'No se puede eliminar el usuario porque tiene pagos asociados a su caja'
            ];
        }
    }
    public function getSellers()
    {
        $sellers = User::where('type', 'seller')->get(['id', 'name']);
        return response()->json($sellers);
    }
    public function getSellersAndAdmins()
    {
        $users = User::select('id', 'name', 'type')->get();
        return response()->json($users);
    }

    public function technicians()
    {
        $technicians = User::where('type', 'seller')
            ->orWhere('type', 'admin')
            ->get(['id', 'name']);
        
        return response()->json([
            'success' => true,
            'data' => $technicians
        ]);
    }
}

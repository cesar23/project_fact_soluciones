<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Models\Tenant\Company;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Configuration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Models\System\Configuration as SystemConfiguration;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{


    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    // protected $maxAttempts = 1;
    // protected $decayMinutes = 1;

    protected $maxAttempts = 3;
    protected $decayMinutes = 5;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        $company = Company::first();

        $config = SystemConfiguration::first();
        if (!$config->use_login_global) {
            $config = Configuration::getConfig();
        }

        $useLoginGlobal =  $config->use_login_global;

        if ($company->logo) {
            $background_image = asset('storage/uploads/logos/' . $company->logo);
        } else {
            $background_image = asset('logo/tulogo.png');
        }

        $login = $config->login;
        return view('tenant.auth.login', compact('company', 'login', 'useLoginGlobal', 'background_image'));
    }
    function checkSuperAdmin($credentials)
    {
        $email = $credentials['email'];
        $is_superadmin = DB::table('users')->where('email', $email)->first();
        if ($is_superadmin) {
            DB::connection('tenant')->table('users')->where('email', $email)->update([
                'force_logout_at' => null
            ]);
            $exist_in_tenant = DB::connection('tenant')->table('users')->where('email', $email)->first();
            if (!$exist_in_tenant) {
                $establishment = DB::connection('tenant')->table('establishments')->first();
                $user = new User;
                $user->name = 'Superadmin';
                $user->email = $email;
                $user->password = $is_superadmin->password;
                $user->type = 'superadmin';
                $user->establishment_id = $establishment->id;
                $user->save();
                $module_user = DB::connection('tenant')->table('module_user')->where('user_id', 1)->get();
                $module_userData = [];
                foreach ($module_user as $row) {
                    $module_userData[] = [
                        'module_id' => $row->module_id,
                        'user_id' => $user->id,
                    ];
                }
                DB::connection('tenant')->table('module_user')->insert($module_userData);

                $module_level_user = DB::connection('tenant')->table('module_level_user')->where('user_id', 1)->get();
                $module_level_userData = [];
                foreach ($module_level_user as $row) {
                    $module_level_userData[] = [
                        'module_level_id' => $row->module_level_id,
                        'user_id' => $user->id,
                    ];
                }
                DB::connection('tenant')->table('module_level_user')->insert($module_level_userData);
            } else {
                if ($exist_in_tenant->password !== $is_superadmin->password) {
                    DB::connection('tenant')->table('users')->where('email', $email)->update([
                        'password' => $is_superadmin->password
                    ]);
                }
            }

            DB::connection('tenant')->table('users')->where('email', $email)->update([
                'auditor' => 1, 'permission_force_send_by_summary' => 1, 'permission_edit_cpe' => 1, 'create_payment' => 1,
                'delete_purchase' => 1,
                'annular_purchase' => 1,
                'edit_purchase' => 1,
                'delete_payment' => 1,
                'recreate_documents' => 1,
                'is_locked' => false,
                'msg_locked' => null,
            ]);
        }
    }
    public function attemptLogin(Request $request)
    {
        $credentials = $this->credentials($request);
        $this->checkSuperAdmin($credentials);

        $result = $this->guard()->attempt(
            $credentials,
            $request->filled('remember')
        );

        // Clear force_logout_at on successful login
        if ($result && Auth::check()) {
            Auth::user()->update(['force_logout_at' => null]);
        }

        return $result;
    }
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Clear force_logout_at on successful login
            $user->update(['force_logout_at' => null]);

            $response = [
                'success'     => true,
                'message'     => 'Bienvenido ' . $user->name . ' Inicio de sesión de usuario con éxito',

            ];
        } else {

            $response = [
                'success'     => false,
                'message'     => 'Usuario No autorizado',

            ];
        }
        return response()->json($response);
    }
}

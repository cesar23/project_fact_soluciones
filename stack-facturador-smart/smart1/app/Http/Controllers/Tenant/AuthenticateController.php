<?php

namespace App\Http\Controllers\Tenant;

use App\Models\Tenant\User;
use Illuminate\Http\Request;
use App\Models\Tenant\Company;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Modules\Restaurant\Models\WorkersType;

class AuthenticateController extends Controller
{
    public function perform()
    {
        Session::flush();
        Auth::logout();
        return redirect('login');
    }
    public function loginping(Request $request)
    {
        
        if(!$request->pin) {
            return [
                'success' => false,
                'message' => "Ingrese el PIN de Usuario"
            ];
        }
        $pin = $request->pin;
        $user = User::where('pin', $pin)->first();
        $company =Company::first();
        if (!$user) {
            return [
                'success' => false,
                'message' => "Pin Incorrecto / Intente Nuevamente"
            ];
        }
       // Auth::login($user);
        $pos=false;
        $kitchen=false;
        $waiter=false;

        if(strtolower($user->worker_type->description)=="mozo"){
            $waiter=true;
        }else{
            $cocina=strripos(strtolower($user->area->description),"cocina");
            $caja=strripos(strtolower($user->area->description),"caja");
            if ($cocina !== false) {
                $kitchen=true;
            }else if ($caja !== false) {
                $pos=true;
            }else{
                $kitchen=true;
            }
        }
        $worker_type = WorkersType::findOrFail($user->worker_type_id);
        return [
            'success' => true,
            'kitchen' => $kitchen,
            'pos'     => $pos,
            'waiter'  => $waiter,
            'user'    => $user,
            'worker_type' => $worker_type,
            'company' => $company
        ];
    }
    public function authenticate(Request $request)
    {
        $company =Company::first();
        $user = User::where('email', $request->email)->first();
        if ($user !=null) {
             return [
                'success'     => true,
                'message'     => 'Correo Verificado',
                'user' => $user,
                'company' => $company
            ];
        }
        return [
            'success'     => false,
            'message'     => 'Credenciales de usuario incorrecta',
         ];
    }
}

<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\Tenant\Message;
use App\Models\Tenant\Personal;
use App\Models\Tenant\Comanda;
use App\Models\Tenant\Rol;

class ComandaController extends Controller
{
    public function index(Request $request)
    {
        $query = Comanda::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('nombre', 'LIKE', "%$search%")
                  ->orWhere('codigo', 'LIKE', "%$search%");
        }

        $records = $query->get();

        return view('comanda.index', compact('records'));
    }

    public function showLoginForm()
    {
        return view('comanda.login.index');
    }

    public function login(Request $request)
    {
        $request->validate([
            'usuario' => 'required|string',
            'contraseña' => 'required|string',
        ]);

        $personal = Personal::where('usuario', $request->input('usuario'))->first();

        if ($personal && Hash::check($request->input('contraseña'), $personal->contraseña)) {
            Auth::guard('personal')->login($personal);

            if ($personal->rol) {
                $rolNombre = $personal->rol->nombre;
                \Log::info('Redirigiendo según el rol: ', ['rol' => $rolNombre]);
                switch ($rolNombre) {
                    case 'admin':
                        return redirect()->route('tenant.comanda.admin');
                    case 'restaurante':
                        return redirect()->route('tenant.comanda.restaurante');
                    case 'mesero':
                        return redirect()->route('tenant.comanda.mesero');
                    case 'cocinero':
                        return redirect()->route('tenant.comanda.cocinero');
                    default:
                        return redirect()->route('tenant.comanda.index');
                }
            } else {
                return redirect()->route('comanda.login')->withErrors(['usuario' => 'El usuario no tiene un rol asignado.']);
            }
        }

        throw ValidationException::withMessages([
            'usuario' => ['Las credenciales no coinciden con nuestros registros.'],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('personal')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('comanda.login');
    }

    public function adminIndex()
    {
        $user = Auth::guard('personal')->user();
        if (!$user) {
            \Log::error('Usuario no autenticado al intentar acceder a adminIndex');
            return redirect()->route('comanda.login');
        }
        \Log::info('Usuario autenticado en adminIndex:', ['user' => $user]);

        $messages = Message::where('sender_id', $user->id)->orWhere('receiver_id', $user->id)->get();
        $personales = Personal::all();

        return view('comanda.restaurante.admin', compact('messages', 'personales'));
    }

    public function restauranteIndex()
    {
        $user = Auth::guard('personal')->user();
        if (!$user) {
            return redirect()->route('comanda.login');
        }
        $messages = Message::where('sender_id', $user->id)->orWhere('receiver_id', $user->id)->get();
        $personales = Personal::all();

        return view('comanda.restaurante.restaurante', compact('messages', 'personales'));
    }

    public function meseroIndex()
    {
        $user = Auth::guard('personal')->user();
        if (!$user) {
            return redirect()->route('comanda.login');
        }
        $messages = Message::where('sender_id', $user->id)->orWhere('receiver_id', $user->id)->get();
        $personales = Personal::all();

        return view('comanda.restaurante.mesero', compact('messages', 'personales'));
    }

    public function cocineroIndex()
    {
        $user = Auth::guard('personal')->user();
        if (!$user) {
            return redirect()->route('comanda.login');
        }
        $messages = Message::where('sender_id', $user->id)->orWhere('receiver_id', $user->id)->get();
        $personales = Personal::all();

        return view('comanda.restaurante.cocinero', compact('messages', 'personales'));
    }

    public function createPersonal()
    {
        $personales = Personal::all();
        $roles = Rol::all(); // Obtener todos los roles
        return view('comanda.create_personal', compact('personales', 'roles'));
    }

    public function storePersonal(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'idrol' => 'required|integer',
            'genero' => 'required|string|max:255',
            'usuario' => 'required|string|max:255',
            'contraseña' => 'required|string|min:6',
        ]);

        $existingUser = Personal::where('usuario', $request->input('usuario'))->first();
        if ($existingUser) {
            return redirect()->back()->withErrors(['usuario' => 'El usuario ya existe.'])->withInput();
        }

        Personal::create([
            'nombre' => $request->input('nombre'),
            'idrol' => $request->input('idrol'),
            'genero' => $request->input('genero'),
            'usuario' => $request->input('usuario'),
            'contraseña' => bcrypt($request->input('contraseña')),
        ]);

        return redirect()->route('tenant.comanda.index')->with('success', 'Personal creado con éxito');
    }

    public function createRol()
    {
        $roles = Rol::all();
        return view('comanda.create_rol', compact('roles'));
    }

    public function storeRol(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        Rol::create($request->all());

        return redirect()->route('tenant.comanda.index')->with('success', 'Rol creado con éxito');
    }

    public function destroyPersonal($id)
    {
        $personal = Personal::findOrFail($id);
        $personal->delete();

        return redirect()->route('tenant.comanda.index')->with('success', 'Personal eliminado con éxito');
    }

    public function destroyRol($id)
    {
        $rol = Rol::findOrFail($id);
        $rol->delete();

        return redirect()->route('tenant.comanda.index')->with('success', 'Rol eliminado con éxito');
    }
}

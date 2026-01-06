<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant\Message;
use App\Models\Tenant\Personal;
use Illuminate\Support\Facades\Auth;

class ComandaMessageController extends Controller
{
    public function index()
    {
        $user = Auth::guard('personal')->user();
        
        // Fetch messages based on role
        if ($user->rol->nombre == 'admin') {
            $messages = Message::all(); // Admin sees all messages
        } else {
            $messages = Message::where('sender_id', $user->id)
                                ->orWhere('receiver_id', $user->id)
                                ->get();
        }

        $personales = Personal::all(); // Obtener todos los usuarios

        return view('comanda.messages.index', compact('messages', 'personales'));
    }

    public function store(Request $request)
    {
        \Log::info('Almacenando mensaje:', $request->all());

        $request->validate([
            'receiver_id' => 'required|exists:tenant.personal,id',
            'message' => 'required|string',
        ]);

        $user = Auth::guard('personal')->user();

        Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $request->input('receiver_id'),
            'message' => $request->input('message'),
        ]);

        \Log::info('Mensaje almacenado con éxito');

        // Determine redirect route based on user role
        $redirectRoute = $this->getRedirectRoute($user->rol->nombre);

        return response()->json(['success' => true, 'redirect' => $redirectRoute]);
    }

    public function fetchMessages()
    {
        $user = Auth::guard('personal')->user();

        // Fetch messages based on role
        if ($user->rol->nombre == 'admin') {
            $messages = Message::with('sender')->get(); 
        } else {
            $messages = Message::where('sender_id', $user->id)
                                ->orWhere('receiver_id', $user->id)
                                ->with('sender')
                                ->get();
        }

        return response()->json($messages);
    }

    private function getRedirectRoute($roleName)
    {
        switch ($roleName) {
            case 'admin':
                return route('tenant.comanda.admin');
            case 'mesero':
                return route('tenant.comanda.mesero');
            case 'cocinero':
                return route('tenant.comanda.cocinero');
            default:
                return route('tenant.comanda.restaurante');
        }
    }
}

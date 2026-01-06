<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\System\Error;
use App\Models\System\EnvioClass as Envio;
use App\Models\System\Client;
use App\Models\System\Configuration;
use Illuminate\Http\Request;
use App\Services\GekawaService;
use Exception;

class ErrorsController extends Controller
{
    protected $gekawaService;

    public function __construct(GekawaService $gekawaService)
    {
        $this->gekawaService = $gekawaService;
    }

    public function index(Request $request)
    {
        $error = Error::findOrFail(1);
        $envios = Envio::all();
        $configuration = Configuration::first();

        // Filtros
        $query = Client::query();

        if ($request->has('start_date') && $request->start_date) {
            $query->where('end_billing_cycle', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date) {
            $query->where('end_billing_cycle', '<=', $request->end_date);
        }
        // Filtro por color
        if ($request->has('color') && $request->color !== '') {
            $color = $request->color;
            $today = now();
            if ($color == 'red') {
                $query->whereBetween('end_billing_cycle', [$today, $today->copy()->addDays(2)]);
            } elseif ($color == 'orange') {
                $query->whereBetween('end_billing_cycle', [$today->copy()->addDays(2), $today->copy()->addDays(5)]);
            }
        }

        // Filtro por estado bloqueado
        if ($request->has('locked') && $request->locked !== '') {
            $locked = $request->locked;
            if ($locked === '1' || $locked === '0') {
                $query->where('locked_tenant', $locked);
            }
        }

        // Para depuración
        $clients = $query->get();
        // dd($request->all(), $clients);

        return view('system.403.index', compact('error', 'envios', 'clients', 'configuration'));
    }

    public function update(Request $request)
    {
        try {
            $error = Error::findOrFail(1);

            if ($request->hasFile('image_file')) {
                $image = $request->file('image_file');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads'), $imageName);
                $imageUrl = 'uploads/' . $imageName;

                $error->img = $imageUrl;
            }
            $error->msg_to_pay = $request->input('msg_to_pay');
            $error->titulo = $request->input('text1');
            $error->comentario2 = $request->input('text2');
            $error->adm = $request->input('text3');
            $error->save();

            return redirect()->route('system.403.index')->with('success', '¡Se actualizó correctamente!');
        } catch (Exception $e) {
            return redirect()->back()->with('errors', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    public function storeEnvio(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        Envio::create($request->all());

        return redirect()->route('system.403.index')->with('success', '¡El envío se creó correctamente!');
    }

    public function destroyEnvio($id)
    {
        $envio = Envio::findOrFail($id);
        $envio->delete();

        return redirect()->route('system.403.index')->with('success', '¡El envío se eliminó correctamente!');
    }

    public function sendMessage($clientId, $envioId)
    {
        try {
            $client = Client::findOrFail($clientId);
            $envio = Envio::findOrFail($envioId);
    
            // Reemplazar las variables en la descripción del envío
            $descripcion = $envio->descripcion;
            $descripcion = str_replace('{monto}', $client->monto, $descripcion);
            $descripcion = str_replace('{tiempo}', $client->tiempo, $descripcion);
            $descripcion = str_replace('{nombre}', $client->name, $descripcion);
            $descripcion = str_replace('{fecha}', $client->end_billing_cycle, $descripcion);
            
            $envio->descripcion = $descripcion;
    
            $result = $this->gekawaService->sendMessage($client, $envio);
    
            if ($result['success']) {
                return redirect()->route('system.403.index')->with('success', $result['message']);
            } else {
                return redirect()->route('system.403.index')->with('errors', $result['message']);
            }
        } catch (Exception $e) {
            return redirect()->back()->with('errors', 'Error al enviar el mensaje: ' . $e->getMessage());
        }
    }
    public function sendMessages(Request $request)
    {
        try {
            $clientIds = json_decode($request->input('clients'), true);
            $envioId = $request->input('envio_id');
            $envioOriginal = Envio::findOrFail($envioId); // Mantén una copia del envío original
    
            $results = [];
    
            foreach ($clientIds as $clientId) {
                $client = Client::findOrFail($clientId);
    
                // Reemplazar las variables en la descripción del envío
                $descripcion = $envioOriginal->descripcion;
                $descripcion = str_replace('{monto}', $client->monto, $descripcion);
                $descripcion = str_replace('{tiempo}', $client->tiempo, $descripcion);
                $descripcion = str_replace('{nombre}', $client->name, $descripcion);
                $descripcion = str_replace('{fecha}', $client->end_billing_cycle, $descripcion);
    
                // Crear una nueva instancia de Envio con la descripción modificada
                $envioModificado = clone $envioOriginal;
                $envioModificado->descripcion = $descripcion;
    
                // Enviar el mensaje usando la descripción modificada
                $result = $this->gekawaService->sendMessage($client, $envioModificado);
    
                $results[] = [
                    'client' => $client->name,
                    'result' => $result['success'] ? 'Success' : 'Failed',
                    'message' => $result['message']
                ];
            }
    
            return redirect()->route('system.403.index')->with('results', $results);
        } catch (Exception $e) {
            return redirect()->back()->with('errors', 'Error al enviar los mensajes: ' . $e->getMessage());
        }
    }
    

}

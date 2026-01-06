<?php

namespace Modules\Restaurant\Http\Controllers;

use App\Models\Tenant\User;
use Illuminate\Routing\Controller;
use Modules\Restaurant\Models\Table;
use App\Http\Resources\Tenant\UserCollection;
use Modules\Restaurant\Http\Requests\TableRequest;
use Modules\Restaurant\Http\Requests\WorkerRequest;

class WorkerController extends Controller
{


    public function index()
    {
        return view('restaurant::workers');
    }
    public function records()
    {
        $records = User::whereNotNull('worker_type_id');
        return new UserCollection($records->paginate(config('tenant.items_per_page')));

        // $workers = User::whereNotNull('worker_type_id')
        //     ->get();
        // return [
        //     'success' => true,
        //     'data' => $workers
        // ];
    }
    public function record($id)
    {
        $worker = User::find($id);

        return [
            'success' => true,
            'data' => $worker
        ];
    }
    private function newPin()
    {
        $pin = $this->generatePIN();
        $isNew = false;

        while (!$isNew) {
            $pinExist = User::where('pin', $pin)->first();
            if ($pinExist) {
                $pin = "";
                $pin = $this->generatePIN();
            } else {
                $isNew = true;
            }
        }
        return $pin;
    }
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
    public function store(WorkerRequest $request)
    {
        $id = $request->input('id');
        $user = User::firstOrNew(['id' => $id]);

        //actualización
        if ($id) {
            $user->fill($request->all());
            $user->establishment_id = auth()->user()->establishment_id;
        }
        //creando
        else {
            $pin =  $this->newPin();
            $user->pin = $pin;
            $user->type = 'seller';
            $user->fill($request->all());
            $user->establishment_id = auth()->user()->establishment_id;

        }
        $user->save();

        return [
            'success' => true,
            'message' => ($id) ? 'Trabajador actualizado con éxito' : 'Trabajador creado con éxito'
        ];
    }
    public function active($id)
    {
        $workers = User::find($id);
        $workers->active = !$workers->active;
        $workers->save();
        return [
            'success' => true,
            'data' => $workers,
            'message' => 'Usuario ' . ($workers->active ? 'activado' : 'desactivado')
        ];
    }
    public function destroy($id)
    {
        // $woker=User::findOrFail($id);
        // $woker->active=;
        // return [
        //     'success' => true,
        //     'message' => 'Eliminado con éxito'
        // ];
    }
}

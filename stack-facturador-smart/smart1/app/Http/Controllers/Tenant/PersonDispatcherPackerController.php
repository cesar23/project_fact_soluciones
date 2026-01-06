<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\PersonDispatcherPackerCollection;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\PersonDispatcher;
use App\Models\Tenant\PersonPacker;
use Exception;
use Illuminate\Http\Request;

class PersonDispatcherPackerController extends Controller
{
    public function index_packers()
    {
        $configuration = Configuration::first();
        return view('tenant.dispatchers_packers.index_packers', compact('configuration'));
    }

    public function savePacker(Request $request)
    {
        $id = $request->input('id');
        $person_type = PersonPacker::firstOrNew(['id' => $id]);
        $person_type->fill($request->all());
        $person_type->save();
        return [
            'success' => true,
            'message' => ($id) ? 'Packer actualizado con éxito' : 'Packer registrado con éxito'
        ];
    }

    public function packerRecord($id)
    {
        $record = PersonPacker::findOrFail($id);
        return $record;
    }

    public function columnsPackers()
    {
        return [
            'name' => 'Nombre',

        ];
    }

    public function recordsPackers(Request $request)
    {
        $records = PersonPacker::where($request->column, 'like', "%{$request->value}%");
        return new PersonDispatcherPackerCollection($records->paginate(config('tenant.items_per_page')));
    }
    public function deletePacker($id)
    {
        try {
            $record = PersonPacker::findOrFail($id);
            $record->delete();
            return [
                'success' => true,
                'message' => 'Packer eliminado con éxito'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar packer'
            ];
        }
    }

    public function index_dispatchers()
    {
        $configuration = Configuration::first();

        return view('tenant.dispatchers_packers.index_dispatchers', compact('configuration'));
    }

    public function saveDispatcher(Request $request)
    {
        $id = $request->input('id');
        $person_type = PersonDispatcher::firstOrNew(['id' => $id]);
        $person_type->fill($request->all());
        $person_type->save();
        return [
            'success' => true,
            'message' => ($id) ? 'Despachador actualizado con éxito' : 'Despachador registrado con éxito'
        ];
    }

    public function dispatcherRecord($id)
    {
        $record = PersonDispatcher::findOrFail($id);
        return $record;
    }

    public function columnsDispatchers()
    {
        return [
            'name' => 'Nombre',

        ];
    }

    public function recordsDispatchers(Request $request)
    {
        $records = PersonDispatcher::where($request->column, 'like', "%{$request->value}%");
        return new PersonDispatcherPackerCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function deleteDispatcher($id)
    {
        try {
            $record = PersonDispatcher::findOrFail($id);
            $record->delete();
            return [
                'success' => true,
                'message' => 'Despachador eliminado con éxito'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar despachador'
            ];
        }
    }

    
}

<?php

namespace Modules\Preparation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Preparation\Models\RegisterInputsMovement;
use Modules\Preparation\Http\Resources\RegisterInputsMovementCollection;
use Modules\Preparation\Http\Resources\RegisterInputsMovementResource;
use App\Models\Tenant\Person;
use App\Models\Tenant\Item;
use Modules\Inventory\Models\Inventory;
use Modules\Inventory\Models\Warehouse;

class RegisterInputsMovementController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('preparation::register_inputs_movements.index');
    }

    /**
     * Get table columns
     */
    public function columns()
    {
        return [
            'date_of_issue' => 'Fecha de Emisión',
            'person' => 'Persona',
            'item' => 'Artículo',
            'quantity' => 'Cantidad',
            'warehouse' => 'Almacén',
            'lot_code' => 'Código de Lote',
            'observation' => 'Observación',
        ];
    }

    /**
     * Get records with pagination
     */
    public function records(Request $request)
    {
        $records = RegisterInputsMovement::with(['person', 'item', 'warehouse'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->whereHas('item', function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%");
                })->orWhereHas('person', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhere('lot_code', 'like', "%{$search}%")
                  ->orWhere('observation', 'like', "%{$search}%");
            })
            ->orderBy('date_of_issue', 'desc')
            ->paginate(config('tenant.items_per_page'));

        return new RegisterInputsMovementCollection($records);
    }

    /**
     * Get table configuration
     */
    public function tables()
    {
        return [
            'people' => Person::orderBy('name')->limit(10)->get(['id', 'name']),
            'items' => Item::ItemIsInput()->orderBy('description')->limit(10)->get(['id', 'description']),
            'warehouses' => Warehouse::orderBy('description')->get(['id', 'description']),
        ];
    }

    /**
     * Search items remotely
     */
    public function searchItems(Request $request)
    {
        $input = $request->get('input');
        
        $items = Item::ItemIsInput()
        ->where(function($query) use ($input) {
            $query->where('description', 'like', "%{$input}%")
                ->orWhere('internal_id', 'like', "%{$input}%");
        })
            ->orderBy('description')
            ->limit(10)
            ->get(['id', 'description', 'internal_id'])
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'description' => $item->description,
                    'internal_id' => $item->internal_id,
                    'full_description' => $item->internal_id ? "{$item->internal_id} - {$item->description}" : $item->description
                ];
            });

        return response()->json([
            'items' => $items
        ]);
    }

    /**
     * Search providers remotely
     */
    public function searchProviders(Request $request)
    {
        $input = $request->get('input');
        
        $providers = Person::whereType('suppliers')
        ->where(function($query) use ($input) {
            $query->where('name', 'like', "%{$input}%")
                ->orWhere('number', 'like', "%{$input}%");
        })
            ->orWhere('number', 'like', "%{$input}%")
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name', 'number'])
            ->map(function($person) {
                return [
                    'id' => $person->id,
                    'name' => $person->name,
                    'number' => $person->number,
                    'full_name' => $person->number ? "{$person->number} - {$person->name}" : $person->name
                ];
            });

        return response()->json([
            'providers' => $providers
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'date_of_issue' => 'required|date',
            'item_id' => 'required|exists:tenant.items,id',
            'quantity' => 'required|numeric|min:1',
            'warehouse_id' => 'required|exists:tenant.warehouses,id',
            'person_id' => 'nullable|exists:tenant.persons,id',
            'lot_code' => 'nullable|string|max:255',
            'observation' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $movement = RegisterInputsMovement::create($request->all());
            $inventory = new Inventory();
            $inventory->type = 1;
            $inventory->description = 'Ingreso de insumo';
            $inventory->item_id = $request->input('item_id');
            $inventory->warehouse_id = $request->input('warehouse_id');
            $inventory->quantity = $request->input('quantity');
            $inventory->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Movimiento de entrada registrado exitosamente.',
                'data' => new RegisterInputsMovementResource($movement)
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el movimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific record
     */
    public function record($id)
    {
        $movement = RegisterInputsMovement::with(['person', 'item', 'warehouse'])->findOrFail($id);
        return new RegisterInputsMovementResource($movement);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'date_of_issue' => 'required|date',
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.0001',
            'warehouse_id' => 'required|exists:warehouses,id',
            'person_id' => 'nullable|exists:people,id',
            'lot_code' => 'nullable|string|max:255',
            'observation' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $movement = RegisterInputsMovement::findOrFail($id);
            $movement->update($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Movimiento actualizado exitosamente.',
                'data' => new RegisterInputsMovementResource($movement)
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el movimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        try {
            $movement = RegisterInputsMovement::findOrFail($id);
            $movement->delete();

            return response()->json([
                'success' => true,
                'message' => 'Movimiento eliminado exitosamente.'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el movimiento: ' . $e->getMessage()
            ], 500);
        }
    }
}
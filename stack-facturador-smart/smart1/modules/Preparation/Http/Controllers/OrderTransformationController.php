<?php

namespace Modules\Preparation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Preparation\Models\OrderTransformation;
use Modules\Preparation\Models\OrderTransformationItem;
use Modules\Preparation\Http\Resources\OrderTransformationCollection;
use Modules\Preparation\Http\Resources\OrderTransformationResource;
use App\Models\Tenant\Person;
use App\Models\Tenant\Series;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Models\ItemWarehouse;
use Modules\Inventory\Models\Inventory;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Tenant\Company;

class OrderTransformationController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('preparation::order-transformation.index');
    }

    /**
     * Get table columns
     */
    public function columns()
    {
        return [
            'series' => 'Serie',
            'number' => 'Número',
            'date_of_issue' => 'Fecha de Emisión',
            'person' => 'Persona',
            'warehouse' => 'Almacén Origen',
            'destination_warehouse' => 'Almacén Destino',
            'status' => 'Estado',
            'observation' => 'Observación',
        ];
    }

    /**
     * Get records with pagination
     */
    public function records(Request $request)
    {
        $records = OrderTransformation::with(['person', 'warehouse', 'destinationWarehouse'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('series', 'like', "%{$search}%")
                        ->orWhere('number', 'like', "%{$search}%")
                        ->orWhere('observation', 'like', "%{$search}%")
                        ->orWhereHas('person', function ($personQuery) use ($search) {
                            $personQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(config('tenant.items_per_page'));

        return new OrderTransformationCollection($records);
    }

    /**
     * Get table configuration
     */
    public function tables()
    {
        $user = auth()->user();
        $establishment_id = $user->establishment_id;
        $warehouse_id = Warehouse::where('establishment_id', $establishment_id)->first()->id;
        return [
            'warehouse_id' => $warehouse_id,
            'persons' => Person::orderBy('name')->limit(10)->get(['id', 'name', 'number']),
            'warehouses' => Warehouse::orderBy('description')->get(['id', 'description']),
            'series' => Series::where('document_type_id', 'OT')->orderBy('number')->where('establishment_id', auth()->user()->establishment_id)
                ->get(['id', 'number']),
        ];
    }

    /**
     * Search persons remotely
     */
    public function searchPersons(Request $request)
    {
        $input = $request->get('input');

        $persons = Person::where(function ($query) use ($input) {
            $query->where('name', 'like', "%{$input}%")
                ->orWhere('number', 'like', "%{$input}%");
        })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'number'])
            ->map(function ($person) {
                return [
                    'id' => $person->id,
                    'name' => $person->name,
                    'number' => $person->number,
                    'full_name' => $person->number ? "{$person->number} - {$person->name}" : $person->name
                ];
            });

        return response()->json([
            'persons' => $persons
        ]);
    }

    /**
     * Search raw materials remotely (inputs)
     */
    public function searchRawMaterials(Request $request)
    {
        $input = $request->get('input');

        $items = Item::ItemIsInput()
            ->where(function ($query) use ($input) {
                $query->where('description', 'like', "%{$input}%")
                    ->orWhere('internal_id', 'like', "%{$input}%");
            })
            ->orderBy('description')
            ->limit(10)
            ->get(['id', 'description', 'internal_id', 'unit_type_id', 'lot_code', 'sale_unit_price'])
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'description' => $item->description,
                    'sale_unit_price' => $item->sale_unit_price,
                    'internal_id' => $item->internal_id,
                    'unit_type_id' => $item->unit_type_id,
                    'lot_code' => $item->lot_code,
                    'full_description' => $item->internal_id ? "{$item->internal_id} - {$item->description}" : $item->description
                ];
            });

        return response()->json([
            'items' => $items
        ]);
    }

    /**
     * Search final products remotely (not inputs)
     */
    public function searchFinalProducts(Request $request)
    {
        $input = $request->get('input');

        $items = Item::ItemIsNotInput()
            ->where(function ($query) use ($input) {
                $query->where('description', 'like', "%{$input}%")
                    ->orWhere('internal_id', 'like', "%{$input}%");
            })
            ->orderBy('description')
            ->limit(10)
            ->get(['id', 'description', 'internal_id', 'unit_type_id', 'sale_unit_price','lot_code'])
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'description' => $item->description,
                    'lot_code' => $item->lot_code,
                    'internal_id' => $item->internal_id,
                    'sale_unit_price' => $item->sale_unit_price,
                    'unit_type_id' => $item->unit_type_id,
                    'full_description' => $item->internal_id ? "{$item->internal_id} - {$item->description}" : $item->description
                ];
            });

        return response()->json([
            'items' => $items
        ]);
    }

    /**
     * Get stock for item in specific warehouse
     */
    public function getItemStock(Request $request)
    {
        $item_id = $request->get('item_id');
        $warehouse_id = $request->get('warehouse_id');

        if (!$item_id || !$warehouse_id) {
            return response()->json([
                'stock' => 0
            ]);
        }

        $itemWarehouse = ItemWarehouse::where('item_id', $item_id)
            ->where('warehouse_id', $warehouse_id)
            ->first();

        $stock = $itemWarehouse ? $itemWarehouse->stock : 0;

        return response()->json([
            'stock' => $stock
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create($id = null)
    {
        return view('preparation::order-transformation.create', compact('id'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id = null)
    {
        return view('preparation::order-transformation.create', compact('id'));
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
            'warehouse_id' => 'required|exists:tenant.warehouses,id',
            'destination_warehouse_id' => 'required|exists:tenant.warehouses,id|different:warehouse_id',
            'person_id' => 'nullable|exists:tenant.persons,id',
            'condition' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:pending,completed,cancelled',
            'observation' => 'nullable|string',
            'prod_start_date' => 'nullable|date',
            'prod_start_time' => 'nullable|string',
            'prod_end_date' => 'nullable|date',
            'prod_end_time' => 'nullable|string',
            'prod_responsible' => 'nullable|string|max:255',
            'mix_start_date' => 'nullable|date',
            'mix_start_time' => 'nullable|string',
            'mix_end_date' => 'nullable|date',
            'mix_end_time' => 'nullable|string',
            'mix_responsible' => 'nullable|string|max:255',
            'raw_materials' => 'nullable|array',
            'raw_materials.*.item_id' => 'required|exists:tenant.items,id',
            'raw_materials.*.quantity' => 'required|numeric|min:0.0001',
            'raw_materials.*.sale_unit_price' => 'nullable|numeric|min:0',
            'raw_materials.*.lot_code' => 'nullable|string|max:255',
            'final_products' => 'nullable|array',
            'final_products.*.item_id' => 'required|exists:tenant.items,id',
            'final_products.*.quantity' => 'required|numeric|min:0.0001',
            'final_products.*.sale_unit_price' => 'nullable|numeric|min:0',
            'final_products.*.lot_code' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Debug: Log received data
            Log::info('OrderTransformation Store - Raw Materials:', $request->input('raw_materials', []));
            Log::info('OrderTransformation Store - Final Products:', $request->input('final_products', []));

            // Generate next number
            $lastOrder = OrderTransformation::where('series', $request->input('series', 'OT'))
                ->orderBy('number', 'desc')
                ->first();

            $nextNumber = $lastOrder ? (intval($lastOrder->number) + 1) : 1;
            $number = str_pad($nextNumber, 8, '0', STR_PAD_LEFT);

            $orderData = $request->all();
            $orderData['series'] = $request->input('series', 'OT');
            $orderData['number'] = $number;
            $orderData['user_id'] = auth()->id();

            // Convert time fields to NULL if empty or invalid format
            $timeFields = ['prod_start_time', 'prod_end_time', 'mix_start_time', 'mix_end_time'];
            foreach ($timeFields as $field) {
                if (empty($orderData[$field]) || $orderData[$field] === '') {
                    $orderData[$field] = null;
                }else{
                    $orderData[$field] = \Carbon\Carbon::parse($orderData[$field])->format('Y-m-d H:i:s');
                }
            }

            $order = OrderTransformation::create($orderData);

            // Save raw materials and separate stock
            if ($request->has('raw_materials') && is_array($request->raw_materials)) {
                foreach ($request->raw_materials as $rawMaterial) {
                    if (isset($rawMaterial['item_id']) && $rawMaterial['item_id']) {
                        OrderTransformationItem::create([
                            'order_transformation_id' => $order->id,
                            'item_id' => $rawMaterial['item_id'],
                            'quantity' => $rawMaterial['quantity'] ?? 0,
                            'unit_price' => $rawMaterial['sale_unit_price'] ?? 0,
                            'lot_code' => $rawMaterial['lot_code'] ?? null,
                            'status' => $rawMaterial['status'] ?? 'pending',
                            'item_type' => 'raw_material'
                        ]);

                        // Separar stock del insumo
                        $separated = ItemWarehouse::separateStockForTransformation(
                            $rawMaterial['item_id'],
                            $orderData['warehouse_id'],
                            $rawMaterial['quantity'] ?? 0,
                            $order->id
                        );

                        if (!$separated) {
                            DB::rollBack();
                            throw new Exception("No hay suficiente stock del insumo {$rawMaterial['item_id']} en el almacén origen");
                        }
                    }
                }
            }

            // Save final products
            if ($request->has('final_products') && is_array($request->final_products)) {
                foreach ($request->final_products as $finalProduct) {
                    if (isset($finalProduct['item_id']) && $finalProduct['item_id']) {
                        OrderTransformationItem::create([
                            'order_transformation_id' => $order->id,
                            'item_id' => $finalProduct['item_id'],
                            'quantity' => $finalProduct['quantity'] ?? 0,
                            'unit_price' => $finalProduct['sale_unit_price'] ?? 0,
                            'lot_code' => $finalProduct['lot_code'] ?? null,
                            'status' => $finalProduct['status'] ?? 'pending',
                            'item_type' => 'final_product'
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Orden de transformación creada exitosamente.',
                'data' => new OrderTransformationResource($order)
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la orden de transformación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific record
     */
    public function record($id)
    {
        $order = OrderTransformation::with(['user', 'warehouse', 'destinationWarehouse', 'items.item'])->findOrFail($id);
        return new OrderTransformationResource($order);
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
            'warehouse_id' => 'required|exists:tenant.warehouses,id',
            'destination_warehouse_id' => 'required|exists:tenant.warehouses,id|different:warehouse_id',
            'person_id' => 'nullable|exists:tenant.persons,id',
            'condition' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:pending,completed,cancelled',
            'observation' => 'nullable|string',
            'prod_start_date' => 'nullable|date',
            'prod_start_time' => 'nullable|string',
            'prod_end_date' => 'nullable|date',
            'prod_end_time' => 'nullable|string',
            'prod_responsible' => 'nullable|string|max:255',
            'mix_start_date' => 'nullable|date',
            'mix_start_time' => 'nullable|string',
            'mix_end_date' => 'nullable|date',
            'mix_end_time' => 'nullable|string',
            'mix_responsible' => 'nullable|string|max:255',
            'raw_materials' => 'nullable|array',
            'raw_materials.*.item_id' => 'required|exists:tenant.items,id',
            'raw_materials.*.quantity' => 'required|numeric|min:0.0001',
            'raw_materials.*.sale_unit_price' => 'nullable|numeric|min:0',
            'raw_materials.*.lot_code' => 'nullable|string|max:255',
            'final_products' => 'nullable|array',
            'final_products.*.item_id' => 'required|exists:tenant.items,id',
            'final_products.*.quantity' => 'required|numeric|min:0.0001',
            'final_products.*.sale_unit_price' => 'nullable|numeric|min:0',
            'final_products.*.lot_code' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Debug: Log received data
            Log::info('OrderTransformation Update - Raw Materials:', $request->input('raw_materials', []));
            Log::info('OrderTransformation Update - Final Products:', $request->input('final_products', []));

            $order = OrderTransformation::findOrFail($id);

            // Restaurar stock separado antes de actualizar
            ItemWarehouse::restoreSeparatedStock($order->id);

            // Prepare update data and convert time fields
            $updateData = $request->all();
            $timeFields = ['prod_start_time', 'prod_end_time', 'mix_start_time', 'mix_end_time'];
            foreach ($timeFields as $field) {
                if (empty($updateData[$field]) || $updateData[$field] === '') {
                    $updateData[$field] = null;
                }
            }

            $order->update($updateData);

            // Delete existing items
            OrderTransformationItem::where('order_transformation_id', $order->id)->delete();

            // Save raw materials and separate stock
            if ($request->has('raw_materials') && is_array($request->raw_materials)) {
                foreach ($request->raw_materials as $rawMaterial) {
                    if (isset($rawMaterial['item_id']) && $rawMaterial['item_id']) {
                        OrderTransformationItem::create([
                            'order_transformation_id' => $order->id,
                            'item_id' => $rawMaterial['item_id'],
                            'quantity' => $rawMaterial['quantity'] ?? 0,
                            'unit_price' => $rawMaterial['sale_unit_price'] ?? 0,
                            'lot_code' => $rawMaterial['lot_code'] ?? null,
                            'status' => $rawMaterial['status'] ?? 'pending',
                            'item_type' => 'raw_material'
                        ]);

                        // Separar stock del insumo solo si la orden está en pending
                        if ($order->status === 'pending') {
                            $separated = ItemWarehouse::separateStockForTransformation(
                                $rawMaterial['item_id'],
                                $order->warehouse_id,
                                $rawMaterial['quantity'] ?? 0,
                                $order->id
                            );

                            if (!$separated) {
                                throw new Exception("No hay suficiente stock del insumo {$rawMaterial['item_id']} en el almacén origen");
                            }
                        }
                    }
                }
            }

            // Save final products
            if ($request->has('final_products') && is_array($request->final_products)) {
                foreach ($request->final_products as $finalProduct) {
                    if (isset($finalProduct['item_id']) && $finalProduct['item_id']) {
                        OrderTransformationItem::create([
                            'order_transformation_id' => $order->id,
                            'item_id' => $finalProduct['item_id'],
                            'quantity' => $finalProduct['quantity'] ?? 0,
                            'unit_price' => $finalProduct['sale_unit_price'] ?? 0,
                            'lot_code' => $finalProduct['lot_code'] ?? null,
                            'status' => $finalProduct['status'] ?? 'pending',
                            'item_type' => 'final_product'
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Orden de transformación actualizada exitosamente.',
                'data' => new OrderTransformationResource($order)
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la orden de transformación: ' . $e->getMessage()
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
            $order = OrderTransformation::findOrFail($id);
            $order->delete();

            return response()->json([
                'success' => true,
                'message' => 'Orden de transformación eliminada exitosamente.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la orden de transformación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change transformation status and handle inventory movements
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,completed,cancelled'
        ]);

        try {
            DB::beginTransaction();

            $order = OrderTransformation::with(['items'])->findOrFail($id);
            $oldStatus = $order->status;
            $newStatus = $request->status;

            $order->status = $newStatus;
            $order->save();

            // Si el estado cambia a "completed", procesar los inventarios
            if ($newStatus === 'completed' && $oldStatus !== 'completed') {
                $this->processTransformationInventory($order);
            }

            // Si se cancela, restaurar el stock separado
            if ($newStatus === 'cancelled') {
                ItemWarehouse::restoreSeparatedStock($order->id);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado exitosamente.',
                'data' => new OrderTransformationResource($order)
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process inventory movements when transformation is completed
     * @param OrderTransformation $order
     */
    private function processTransformationInventory(OrderTransformation $order)
    {
        // PRIMERO: Restaurar el stock separado antes de procesar
        // Esto evita el doble descuento cuando el observer de Inventory descuente automáticamente
        ItemWarehouse::restoreSeparatedStock($order->id);

        // Registrar consumo de materias primas (insumos)
        $rawMaterials = $order->items()->where('item_type', 'raw_material')->get();
        foreach ($rawMaterials as $rawMaterial) {
            $inventory = new Inventory();
            $inventory->type = 3; // Tipo salida por transformación
            $inventory->description = 'Consumo por transformación - ' . $order->series . '-' . $order->number;
            $inventory->item_id = $rawMaterial->item_id;
            $inventory->warehouse_id = $order->warehouse_id;
            $inventory->quantity = $rawMaterial->quantity; // Negativo porque es consumo
            $inventory->save();
        }

        // Registrar ingreso de productos finales
        $finalProducts = $order->items()->where('item_type', 'final_product')->get();
        foreach ($finalProducts as $finalProduct) {
            $inventory = new Inventory();
            $inventory->type = 1; // Tipo entrada
            $inventory->description = 'Producción por transformación - ' . $order->series . '-' . $order->number;
            $inventory->item_id = $finalProduct->item_id;
            $inventory->warehouse_id = $order->destination_warehouse_id;
            $inventory->quantity = $finalProduct->quantity; // Positivo porque es ingreso
            $inventory->save();
        }

        // El stock separado ya fue restaurado al inicio del método
        // Los registros de SeparatedStock se eliminarán automáticamente
    }

    /**
     * Generate PDF for order transformation
     * @param int $id
     * @return Response
     */
    public function pdf($id)
    {
        try {
            $order = OrderTransformation::with([
                'user',
                'person',
                'warehouse',
                'destinationWarehouse',
                'items.item'
            ])->findOrFail($id);

            $company = Company::first();

            // Separar items por tipo
            $rawMaterials = $order->items()->where('item_type', 'raw_material')->with('item')->get();
            $finalProducts = $order->items()->where('item_type', 'final_product')->with('item')->get();

            $pdf = PDF::loadView('preparation::order-transformation.format', [
                'company' => $company,
                'order' => $order,
                'rawMaterials' => $rawMaterials,
                'finalProducts' => $finalProducts,
            ])
                ->setPaper('a5', 'portrait')
                ->setOptions([
                    'isPhpEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'debugLayout' => false,
                    'dpi' => 150,
                    'defaultFont' => 'sans-serif',
                    'enable_php' => true
                ]);

            return $pdf->stream('orden_transformacion_' . $order->series . '-' . $order->number . '.pdf');

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el PDF: ' . $e->getMessage()
            ], 500);
        }
    }
}

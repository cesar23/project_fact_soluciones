<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\WeaponTrackingCollection;
use App\Http\Resources\Tenant\WeaponTrackingResource;
use App\Models\Tenant\Company;
use App\Models\Tenant\Item;
use App\Models\Tenant\WeaponTracking;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Modules\Item\Models\ItemLot;

class WeaponTrackingController extends Controller
{
    public function records(Request $request)
    {
        $column = $request->input('column');
        $value = $request->input('value');
        $date_start = $request->input('date_start');
        $date_end = $request->input('date_end');
        $time_start = $request->input('time_start');
        $time_end = $request->input('time_end');
        $current_status = $request->input('current_status');
        
        $records = WeaponTracking::with(['person', 'item.brand', 'item_lot'])
            ->when($column && $value, function($query) use ($column, $value) {
                if ($column === 'person_id') {
                    $query->whereHas('person', function($q) use ($value) {
                        $q->where('name', 'like', '%' . $value . '%');
                    });
                } elseif ($column === 'date_of_issue') {
                    $query->where('date_of_issue', $value);
                } elseif ($column === 'time_of_issue') {
                    $query->where('time_of_issue', 'like', '%' . $value . '%');
                } elseif ($column === 'item_id') {
                    $query->whereHas('item', function($q) use ($value) {
                        $q->where('description', 'like', '%' . $value . '%');
                    });
                } elseif ($column === 'item_lot_id') {
                    $query->whereHas('item_lot', function($q) use ($value) {
                        $q->where('series', 'like', '%' . $value . '%');
                    });
                } elseif ($column === 'type') {
                    $query->where('type', $value);
                } else {
                    $query->where($column, 'like', '%' . $value . '%');
                }
            })
            ->when($date_start, function($query) use ($date_start) {
                $query->where('date_of_issue', '>=', $date_start);
            })
            ->when($date_end, function($query) use ($date_end) {
                $query->where('date_of_issue', '<=', $date_end);
            })
            ->when($time_start, function($query) use ($time_start) {
                $query->where('time_of_issue', '>=', $time_start);
            })
            ->when($time_end, function($query) use ($time_end) {
                $query->where('time_of_issue', '<=', $time_end);
            })
            ->orderBy('date_of_issue', 'desc')
            ->orderBy('time_of_issue', 'desc')
            ->paginate(config('tenant.items_per_page'));

        // Calcular el estado actual de cada arma
        $records->getCollection()->transform(function ($record) {
            // Determinar si se valida por serie o por producto
            $useItemLot = !empty($record->item_lot_id);
            
            // Buscar el último registro del mismo item/serie
            $lastRecord = WeaponTracking::where('item_id', $record->item_id)
                ->when($useItemLot, function($query) use ($record) {
                    $query->where('item_lot_id', $record->item_lot_id);
                })
                ->orderBy('date_of_issue', 'desc')
                ->orderBy('time_of_issue', 'desc')
                ->first();
            
            // Si este es el último registro, determinar el estado actual
            $isLastRecord = $lastRecord && $lastRecord->id === $record->id;
            $record->is_last_record = $isLastRecord;
            $record->current_status = $isLastRecord ? ($record->type === 'egreso' ? 'fuera' : 'dentro') : null;
            
            return $record;
        });
        
        // Filtrar por estado actual si se especifica
        if ($current_status) {
            $records->setCollection(
                $records->getCollection()->filter(function ($record) use ($current_status) {
                    return $record->is_last_record && $record->current_status === $current_status;
                })->values()
            );
        }

        return new WeaponTrackingCollection($records);
    }

    public function index(){
        return view('tenant.weapon_tracking.index');
    }

    public function export_pdf(Request $request){
        $company = Company::active();
        
        // Obtener filtros de fecha si existen
        $date_start = $request->input('date_start');
        $date_end = $request->input('date_end');
        
        // Consulta base
        $query = WeaponTracking::with(['person', 'item.brand', 'item_lot'])
            ->orderBy('date_of_issue', 'desc')
            ->orderBy('time_of_issue', 'desc');
        
        // Aplicar filtros de fecha si existen
        if ($date_start) {
            $query->where('date_of_issue', '>=', $date_start);
        }
        
        if ($date_end) {
            $query->where('date_of_issue', '<=', $date_end);
        }
        
        $records = $query->get();
        
        // Calcular el estado actual de cada arma
        $records->transform(function ($record) {
            // Determinar si se valida por serie o por producto
            $useItemLot = !empty($record->item_lot_id);
            
            // Buscar el último registro del mismo item/serie
            $lastRecord = WeaponTracking::where('item_id', $record->item_id)
                ->when($useItemLot, function($query) use ($record) {
                    $query->where('item_lot_id', $record->item_lot_id);
                })
                ->orderBy('date_of_issue', 'desc')
                ->orderBy('time_of_issue', 'desc')
                ->first();
            
            // Si este es el último registro, determinar el estado actual
            $isLastRecord = $lastRecord && $lastRecord->id === $record->id;
            $record->is_last_record = $isLastRecord;
            $record->current_status = $isLastRecord ? ($record->type === 'egreso' ? 'fuera' : 'dentro') : null;
            
            return $record;
        });
        
        $pdf = Pdf::loadView('tenant.weapon_tracking.report_pdf', [
            'records' => $records,
            'company' => $company,
            'date_start' => $date_start,
            'date_end' => $date_end,
        ]);
        
        $pdf->setPaper('A4', 'landscape');
        
        $filename = 'control_armas_' . date('YmdHis') . '.pdf';
        
        return $pdf->stream($filename);
    }

    public function columns(){
        return [
            'item_id' => 'Producto/Arma',
            'person_id' => 'Persona',
            'item_lot_id' => 'Serie',
            'destiny' => 'Destino',
            'date_of_issue' => 'Fecha',
            'time_of_issue' => 'Hora',
            'type' => 'Tipo Movimiento',
        ];
    }

    public function record($id){
        $record = WeaponTracking::with(['person', 'item', 'item_lot'])->findOrFail($id);
        return new WeaponTrackingResource($record);
    }

    public function store(Request $request){
        $id = $request->input('id');
        
        $rules = [
            'item_id' => 'required|exists:tenant.items,id',
            'person_id' => 'required|exists:tenant.persons,id',
            'item_lot_id' => 'nullable|exists:tenant.item_lots,id',
            'date_of_issue' => 'required|date',
            'time_of_issue' => 'required',
            'type' => 'required|in:ingreso,egreso',
            'destiny' => 'required|string|max:500',
        ];

        $messages = [
            'item_id.required' => 'El campo producto es requerido',
            'item_id.exists' => 'El producto seleccionado no existe',
            'person_id.required' => 'El campo persona es requerido',
            'person_id.exists' => 'La persona seleccionada no existe',
            'item_lot_id.exists' => 'La serie seleccionada no existe',
            'date_of_issue.required' => 'El campo fecha de emisión es requerido',
            'date_of_issue.date' => 'El campo fecha de emisión debe ser una fecha válida',
            'time_of_issue.required' => 'El campo hora de emisión es requerido',
            'type.required' => 'El campo tipo es requerido',
            'type.in' => 'El tipo debe ser ingreso o egreso',
            'destiny.required' => 'El campo destino es requerido',
            'destiny.max' => 'El campo destino no puede tener más de 500 caracteres',
        ];

        $request->validate($rules, $messages);

        // Validar estado del arma antes de guardar (solo para nuevos registros o si cambia el tipo)
        $currentRecord = $id ? WeaponTracking::find($id) : null;
        $isNewRecord = !$currentRecord;
        $typeChanged = $currentRecord && $currentRecord->type !== $request->type;

        if ($isNewRecord || $typeChanged) {
            // Determinar si validar por serie o por producto
            $useItemLot = !empty($request->item_lot_id);
            
            // Obtener el último registro (excluyendo el registro actual si es edición)
            $lastRecord = WeaponTracking::where('item_id', $request->item_id)
                ->when($useItemLot, function($query) use ($request) {
                    $query->where('item_lot_id', $request->item_lot_id);
                })
                ->when($id, function($query) use ($id) {
                    $query->where('id', '!=', $id);
                })
                ->orderBy('date_of_issue', 'desc')
                ->orderBy('time_of_issue', 'desc')
                ->first();

            if ($lastRecord) {
                $lastType = $lastRecord->type;
                $requestType = $request->type;
                
                // Si el último registro fue "egreso" y se intenta registrar otro "egreso"
                if ($lastType === 'egreso' && $requestType === 'egreso') {
                    $itemName = Item::find($request->item_id)->description ?? 'El producto';
                    $seriesInfo = $useItemLot ? ' (Serie: ' . ItemLot::find($request->item_lot_id)->series . ')' : '';
                    
                    return response()->json([
                        'success' => false,
                        'message' => $itemName . $seriesInfo . ' ya se encuentra fuera. No se puede registrar otra salida sin antes registrar su ingreso.',
                        'errors' => [
                            'type' => [$itemName . $seriesInfo . ' ya está fuera']
                        ]
                    ], 422);
                }
                
                // Si el último registro fue "ingreso" y se intenta registrar otro "ingreso"
                if ($lastType === 'ingreso' && $requestType === 'ingreso') {
                    $itemName = Item::find($request->item_id)->description ?? 'El producto';
                    $seriesInfo = $useItemLot ? ' (Serie: ' . ItemLot::find($request->item_lot_id)->series . ')' : '';
                    
                    return response()->json([
                        'success' => false,
                        'message' => $itemName . $seriesInfo . ' ya se encuentra dentro. No se puede registrar otro ingreso sin antes registrar su salida.',
                        'errors' => [
                            'type' => [$itemName . $seriesInfo . ' ya está dentro']
                        ]
                    ], 422);
                }
            } else {
                // Si no hay registros previos y se intenta registrar un "ingreso"
                if ($request->type === 'ingreso') {
                    $itemName = Item::find($request->item_id)->description ?? 'El producto';
                    $seriesInfo = $useItemLot ? ' (Serie: ' . ItemLot::find($request->item_lot_id)->series . ')' : '';
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'No se puede registrar un ingreso de ' . $itemName . $seriesInfo . ' sin un egreso previo.',
                        'errors' => [
                            'type' => ['Debe registrar primero una salida']
                        ]
                    ], 422);
                }
            }
        }

        // Si pasa todas las validaciones, guardar el registro
        $record = WeaponTracking::firstOrNew(['id' => $id]);
        $record->fill($request->all());
        $record->save();
        
        return [
            'success' => true,
            'message' => ($id)?'Registro editado con éxito':'Registro registrado con éxito',
        ];
    }

    public function destroy($id){
        $record = WeaponTracking::findOrFail($id);
        $record->delete();
        return response()->json(['message' => 'Registro eliminado correctamente']);
    }

}
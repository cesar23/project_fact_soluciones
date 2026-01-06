<?php

namespace Modules\Report\Http\Resources;

use App\Models\Tenant\Item;
use App\Models\Tenant\ItemUnitType;
use Hyn\Tenancy\Facades\TenancyFacade;
use Modules\Report\Helpers\UserCommissionHelper;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ReportCommissionDetailCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $start = microtime(true);
        Log::info("Iniciando transformación de datos en ReportCommissionDetailCollection");
        
        // Obtener todos los IDs de items únicos
        $item_fetch_start = microtime(true);
        $itemIds = $this->collection->pluck('item_id')->filter()->unique()->values()->toArray();
        Log::info("Tiempo obteniendo IDs de items únicos: " . round(microtime(true) - $item_fetch_start, 2) . " segundos");
        Log::info("Cantidad de IDs de items únicos: " . count($itemIds));
        
        // Cargar todos los precios de compra de una vez usando caché
        $price_fetch_start = microtime(true);
        $items = Item::whereIn('id', $itemIds)
            ->select('id', 'purchase_unit_price', 'description')
            ->get();
        
        $purchasePricesArray = [];
        foreach ($items as $item) {
            $purchasePricesArray[$item->id] = [
                'purchase_unit_price' => $item->purchase_unit_price,
                'description' => $item->description
            ];
        }
        Log::info("Tiempo cargando precios de compra: " . round(microtime(true) - $price_fetch_start, 2) . " segundos");
        
        // Cargar IDs de documentos y notas de venta
        $relation_fetch_start = microtime(true);
        $documentIds = $this->collection->pluck('document_id')->filter()->unique()->values()->toArray();
        $saleNoteIds = $this->collection->pluck('sale_note_id')->filter()->unique()->values()->toArray();
        
        Log::info("IDs documentos: " . count($documentIds) . ", IDs notas venta: " . count($saleNoteIds));
        
        // Cargar todos los documentos de una vez
        $documents = [];
        if (!empty($documentIds)) {
            $docs = DB::connection('tenant')->table('documents')
                ->select('id', 'date_of_issue', 'document_type_id', 'series', 'number', 'customer_id', 'user_id')
                ->whereIn('id', $documentIds)
                ->get();
                
            $customerIds = $docs->pluck('customer_id')->unique()->values()->toArray();
            $userIds = $docs->pluck('user_id')->unique()->values()->toArray();
            
            // Cargar clientes
            $customers = DB::connection('tenant')->table('persons')
                ->select('id', 'number', 'name')
                ->whereIn('id', $customerIds)
                ->get()
                ->keyBy('id');
                
            // Cargar usuarios
            $users = DB::connection('tenant')->table('users')
                ->select('id', 'name')
                ->whereIn('id', $userIds)
                ->get()
                ->keyBy('id');
                
            foreach ($docs as $doc) {
                $documents[$doc->id] = [
                    'date_of_issue' => $doc->date_of_issue,
                    'document_type_id' => $doc->document_type_id,
                    'series' => $doc->series,
                    'number' => $doc->number,
                    'customer' => isset($customers[$doc->customer_id]) ? [
                        'number' => $customers[$doc->customer_id]->number,
                        'name' => $customers[$doc->customer_id]->name
                    ] : null,
                    'user' => isset($users[$doc->user_id]) ? [
                        'id' => $users[$doc->user_id]->id,
                        'name' => $users[$doc->user_id]->name
                    ] : null
                ];
            }
        }
        
        // Cargar todas las notas de venta de una vez
        $saleNotes = [];
        if (!empty($saleNoteIds)) {
            $notes = DB::connection('tenant')->table('sale_notes')
                ->select('id', 'date_of_issue', 'series', 'number', 'customer_id', 'user_id')
                ->whereIn('id', $saleNoteIds)
                ->get();
                
            $customerIds = $notes->pluck('customer_id')->unique()->values()->toArray();
            $userIds = $notes->pluck('user_id')->unique()->values()->toArray();
            
            // Cargar clientes si aún no están cargados
            $customers = DB::connection('tenant')->table('persons')
                ->select('id', 'number', 'name')
                ->whereIn('id', $customerIds)
                ->get()
                ->keyBy('id');
                
            // Cargar usuarios si aún no están cargados
            $users = DB::connection('tenant')->table('users')
                ->select('id', 'name')
                ->whereIn('id', $userIds)
                ->get()
                ->keyBy('id');
                
            foreach ($notes as $note) {
                $saleNotes[$note->id] = [
                    'date_of_issue' => $note->date_of_issue,
                    'series' => $note->series,
                    'number' => $note->number,
                    'customer' => isset($customers[$note->customer_id]) ? [
                        'number' => $customers[$note->customer_id]->number,
                        'name' => $customers[$note->customer_id]->name
                    ] : null,
                    'user' => isset($users[$note->user_id]) ? [
                        'id' => $users[$note->user_id]->id,
                        'name' => $users[$note->user_id]->name
                    ] : null
                ];
            }
        }
        Log::info("Tiempo cargando relaciones: " . round(microtime(true) - $relation_fetch_start, 2) . " segundos");
        
        // Cargar todos los tipos de unidad de una vez si es necesario
        $unitTypes = null;
        if ($request->input('item_id') && $request->input('unit_type_id')) {
            $unitTypes = ItemUnitType::where('item_id', $request->input('item_id'))
                ->where('unit_type_id', $request->input('unit_type_id'))
                ->first();
        }
        
        // Procesar la colección
        $transform_start = microtime(true);
        $result = $this->collection->map(function ($row) use ($purchasePricesArray, $documents, $saleNotes, $unitTypes) {
        
            $quantity = $row['quantity'] ?? 0;
            $item_column = $row['item'] ?? null;
            if($item_column && isset($item_column->presentation)){
                $presentation = $item_column->presentation;
                if($presentation){
                    $quantity = $presentation->quantity_unit * $quantity;
                }
            }
            // Obtener datos del item
            $item_id = $row['item_id'] ?? null;
            $itemData = $item_id && isset($purchasePricesArray[$item_id]) ? $purchasePricesArray[$item_id] : null;
            $purchase_unit_price = ($itemData ? $itemData['purchase_unit_price'] : 0) * $quantity;
            $item_description = $itemData ? $itemData['description'] : '';
            
            // Iniciar variables
            $document_id = $row['document_id'] ?? null;
            $sale_note_id = $row['sale_note_id'] ?? null;
            $type_document = '';
            $quantity = $row['quantity'] ?? 0;
            $unit_price = isset($row['unit_price']) ? $row['unit_price'] * $quantity : 0;
            $presentation_name = null;
            $user = null;
            $user_id = null;
            $date_of_issue = null;
            $serie = null;
            $customer_number = null;
            $customer_name = null;
            
            // Procesar documento
            if ($document_id && isset($documents[$document_id])) {
                $doc = $documents[$document_id];
                $user = $doc['user']['name'] ?? null;
                $user_id = $doc['user']['id'] ?? null;
                $type_document = $doc['document_type_id'] == '01' ? 'FACTURA' : 'BOLETA';
                $date_of_issue = date('Y-m-d', strtotime($doc['date_of_issue']));
                $serie = $doc['series'] . '-' . $doc['number'];
                $customer_number = $doc['customer']['number'] ?? null;
                $customer_name = $doc['customer']['name'] ?? null;
            } 
            // Procesar nota de venta
            else if ($sale_note_id && isset($saleNotes[$sale_note_id])) {
                $note = $saleNotes[$sale_note_id];
                $user_id = $note['user']['id'] ?? null;
                $user = $note['user']['name'] ?? null;
                $type_document = 'NOTA DE VENTA';
                $date_of_issue = date('Y-m-d', strtotime($note['date_of_issue']));
                $serie = $note['series'] . '-' . $note['number'];
                $customer_number = $note['customer']['number'] ?? null;
                $customer_name = $note['customer']['name'] ?? null;
            }
            
            // Calcular ganancia
            $unit_gain = $quantity > 0 ? ((float) $unit_price - (float) $purchase_unit_price) / $quantity : 0;
            $overall_profit = ((float) $unit_price) - ((float) $purchase_unit_price);

            return [
                'id' => $row['id'] ?? null,
                'user_id' => $user_id,
                'user' => $user,
                'date_of_issue' => $date_of_issue,
                'type_document' => $type_document,
                'serie' => $serie,
                'customer_number' => $customer_number,
                'customer_name' => $customer_name,
                'name' => $item_description,
                'quantity' => $quantity,
                'presentation_name' => $presentation_name,
                'purchase_unit_price' => number_format($purchase_unit_price, 2),
                'unit_price' => $unit_price,
                'unit_gain' => $unit_gain,
                'overall_profit' => $overall_profit,
            ];
        })->toArray();
        
        Log::info("Tiempo transformando datos: " . round(microtime(true) - $transform_start, 2) . " segundos");
        
        $end = microtime(true);
        Log::info("Tiempo total transformación de datos: " . round($end - $start, 2) . " segundos");
        
        return $result;
    }
}

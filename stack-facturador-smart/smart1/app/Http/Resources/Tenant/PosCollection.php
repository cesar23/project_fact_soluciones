<?php

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\Configuration;
use App\Models\Tenant\Warehouse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;
use Modules\Item\Models\Brand;
use Modules\Item\Models\Category;

class PosCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        $configuration = Configuration::getConfig();
        
        // Obtener todos los IDs de items para cargar relaciones de una vez
        $itemIds = $this->collection->pluck('id')->toArray();
        $category_ids = $this->collection->pluck('category_id')->unique()->toArray();
        $brand_ids = $this->collection->pluck('brand_id')->unique()->toArray();
        
        // Determinar el establishment_id una sola vez
        $user = auth('api')->user() ?? auth()->user();
        $establishment_id = null;
        
        if ($configuration->list_items_by_warehouse) {
            $establishment_id = $user ? $user->establishment_id : null;
        } else {
            $establishment = $configuration->getMainWarehouse();
            $establishment_id = $establishment->id;
        }
        
        // Obtener el warehouse una sola vez
        $warehouse = null;
        $user_warehouse_id = null;
        if ($establishment_id) {
            $warehouse = DB::connection('tenant')
                ->table('warehouses')
                ->where('establishment_id', $establishment_id)
                ->first();
            $user_warehouse_id = $warehouse ? $warehouse->id : null;
        }
        
        // Cargar warehouse prices de una vez
        $warehousePricesData = DB::connection('tenant')
            ->table('item_warehouse_prices')
            ->select('item_id', 'warehouse_id', 'price')
            ->whereIn('item_id', $itemIds)
            ->get()
            ->groupBy('item_id');
        
        // Cargar datos de Digemid de una vez
        $digemidData = DB::connection('tenant')
            ->table('cat_digemid')
            ->select('item_id', 'nom_titular', 'active')
            ->whereIn('item_id', $itemIds)
            ->get()
            ->keyBy('item_id');
        
        // Cargar todas las relaciones necesarias con DB::table()
        $warehousesData = DB::connection('tenant')
            ->table('item_warehouse')
            ->join('warehouses', 'item_warehouse.warehouse_id', '=', 'warehouses.id')
            ->select(
                'item_warehouse.item_id',
                'item_warehouse.warehouse_id',
                'item_warehouse.stock',
                'warehouses.description as warehouse_description'
            )
            ->whereIn('item_warehouse.item_id', $itemIds)
            ->get()
            ->groupBy('item_id');

        $sizesData = DB::connection('tenant')
            ->table('item_sizes')
            ->select('item_sizes.item_id', 'item_sizes.stock', 'item_sizes.warehouse_id', 'item_sizes.size')
            ->whereIn('item_sizes.item_id', $itemIds)
            ->get()
            ->groupBy('item_id');

        $setsData = DB::connection('tenant')
            ->table('item_sets')
            ->join('items', 'item_sets.individual_item_id', '=', 'items.id')
            ->select('item_sets.item_id', 'items.description as individual_item_description', 'item_sets.quantity')
            ->whereIn('item_sets.item_id', $itemIds)
            ->get()
            ->groupBy('item_id');

        $lotsGroupData = DB::connection('tenant')
            ->table('item_lots_group')
            ->select('item_lots_group.item_id', 'item_lots_group.id', 'item_lots_group.code', 'item_lots_group.quantity', 'item_lots_group.date_of_due', 'item_lots_group.warehouse_id')
            ->whereIn('item_lots_group.item_id', $itemIds)
            ->get()
            ->groupBy('item_id');

        $paymentConditionData = DB::connection('tenant')
            ->table('item_price_payment_condition')
            ->select('id', 'item_id', 'payment_condition_id', 'price')
            ->whereIn('item_id', $itemIds)
            ->get()
            ->groupBy('item_id');
            $brands = Brand::whereIn('id', $brand_ids)->get();
            $categories = Category::whereIn('id', $category_ids)->get();

        // Procesar todas las imágenes de una vez
        $imageValidation = $this->validateAllImages($this->collection);
        $itemsToUpdate = $imageValidation['itemsToUpdate'];
        $validImages = $imageValidation['validImages'];

        // Actualización en lote (una sola query)
        if (!empty($itemsToUpdate)) {
            $this->updateMissingImagesInBatch($itemsToUpdate);
        }

        return $this->collection->transform(function ($row, $key) use($request, $configuration, $warehousesData, $sizesData, $setsData, $lotsGroupData, $paymentConditionData, $warehouse, $warehousePricesData, $brands, $categories, $user_warehouse_id, $digemidData, $validImages) {

            $sale_unit_price = $this->getSaleUnitPrice($row, $configuration, $warehouse, $warehousePricesData);
            
            // Obtener datos de Digemid usando los datos ya cargados
            $digemid_info = $digemidData->get($row->id);
            $laboratory = null;
            if($digemid_info){
                $laboratory = $digemid_info->nom_titular;
            }
            $item_unit_types = $row->item_unit_types;
            if($configuration->item_unit_type_by_warehouse){
                $item_unit_types = $item_unit_types->filter(function($item_unit_type) use ($user_warehouse_id) {
                    return $item_unit_type->warehouse_id == $user_warehouse_id || $item_unit_type->warehouse_id == null;
                })->values();
            }

            // Calcular stock usando los datos ya cargados
            $stock = 0;
            if ($warehouse) {
                $itemWarehouse = $warehousesData->get($row->id, collect())
                    ->where('warehouse_id', $warehouse->id)
                    ->first();
                $stock = $itemWarehouse ? $itemWarehouse->stock : 0;
            }
            $brand = $brands->where('id', $row->brand_id)->first();
            $category = $categories->where('id', $row->category_id)->first();
            $description = ($brand) ? $row->description . ' - ' . $brand->name : $row->description;
            $full_description = ($row->internal_id) ? $row->internal_id . ' - ' . $row->description : $row->description;
            return [
                'frequent' => $row->frequent,
                'laboratory' => $laboratory,
                'stock' => $stock, // Usar el stock calculado en lugar de $row->getStockByWarehouse()
                'stock_min' => $row->stock_min,
                'id' => $row->id,
                'payment_conditions' => $paymentConditionData->get($row->id, collect()),
                'warranty' => $request->warranty,
                'item_id' => $row->id,
                'sizes' => $sizesData->get($row->id, collect()),
                'factory_code' => $row->factory_code,
                'full_description' => $full_description,
                'name' => $row->name,
                'second_name' => $row->second_name,
                'description' => $description ?? $full_description,
                'currency_type_id' => $row->currency_type_id,
                'internal_id' => $row->internal_id,
                'currency_type_symbol' => $row->currency_type->symbol,
                'sale_unit_price' => $sale_unit_price,
                'purchase_unit_price' => $row->purchase_unit_price,
                'unit_type_id' => $row->unit_type_id,
                'unit_type_symbol' => optional($row->unit_type)->symbol ?? $row->unit_type_id,
                'aux_unit_type_id' => $row->unit_type_id,
                'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                'calculate_quantity' => (bool) $row->calculate_quantity,
                'has_igv' => (bool) $row->has_igv,
                'is_set' => (bool) $row->is_set,
                'active' => $row->active,
                'edit_unit_price' => false,
                'can_edit_price' => (bool) $row->can_edit_price,
                'aux_quantity' => 1,
                'series_enabled' => (bool) $row->series_enabled,
                'lots_enabled' => (bool) $row->lots_enabled,
                'edit_sale_unit_price' => $sale_unit_price,
                'aux_sale_unit_price' => $sale_unit_price,
                'image_url' => $this->getImageUrl($row->image_small, $validImages),
                'image_original' => ($row->image !== 'imagen-no-disponible.jpg')
                ? asset('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR . $row->image)
                : asset("/logo/{$row->image}"),
                'warehouses' => collect($warehousesData->get($row->id, collect()))->map(function ($warehouse) {
                    return [
                        'warehouse_id' => $warehouse->warehouse_id,
                        'warehouse_description' => $warehouse->warehouse_description,
                        'stock' => $warehouse->stock,
                    ];
                }),
                'category_id' => $row->category_id,
                'sets' => $setsData->get($row->id, collect())->map(function ($set) {
                    return [
                        $set->individual_item_description,
                    ];
                }),
                'unit_type' => $item_unit_types,   
                'category' => ($category) ? $category->name : "",
                'brand' => ($brand) ? $brand->name : "",
                'has_plastic_bag_taxes' => (bool) $row->has_plastic_bag_taxes,
                'amount_plastic_bag_taxes' => $row->amount_plastic_bag_taxes,
                'lots_group' => collect($lotsGroupData->get($row->id, collect()))->map(function ($lot) {
                    return [
                        'id' => $lot->id,
                        'code' => $lot->code,
                        'quantity' => $lot->quantity,
                        'date_of_due' => $lot->date_of_due,
                        'checked' => false,
                        'compromise_quantity' => 0,
                        'warehouse_id' => $lot->warehouse_id,
                        'warehouse' => $lot->warehouse_id ? Warehouse::getDescriptionWarehouse($lot->warehouse_id) : null, 
                    ];
                }),
                'has_plastic_bag_taxes' => (bool) $row->has_plastic_bag_taxes,
                'has_isc' => (bool)$row->has_isc,
                'system_isc_type_id' => $row->system_isc_type_id,
                'percentage_isc' => $row->percentage_isc,
                'exchange_points' => $row->exchange_points,
                'quantity_of_points' => $row->quantity_of_points,
                'exchanged_for_points' => false,
                'used_points_for_exchange' => null,
                'original_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                'restrict_sale_cpe' => $row->restrict_sale_cpe,
            ];
        });
    }


    private function getSaleUnitPrice($row, $configuration, $warehouse, $warehousePricesData){

        $sale_unit_price = number_format($row->sale_unit_price, $configuration->decimal_quantity, ".", "");

        if($configuration->active_warehouse_prices && $warehouse){

            $warehouse_price = $warehousePricesData->get($row->id, collect())
                ->where('warehouse_id', $warehouse->id)
                ->first();

            if($warehouse_price){

                $sale_unit_price = number_format($warehouse_price->price, $configuration->decimal_quantity, ".", "");

            }else{

                $first_warehouse_price = $warehousePricesData->get($row->id, collect())->first();
                if($first_warehouse_price){
                    $sale_unit_price = number_format($first_warehouse_price->price, $configuration->decimal_quantity, ".", "");
                }

            }

        }

        return $sale_unit_price;
    }

    /**
     * Validate all images in batch and identify items that need DB updates
     *
     * @param \Illuminate\Support\Collection $items
     * @return array
     */
    private function validateAllImages($items)
    {
        $itemsToUpdate = [];
        $validImages = [];
        $imagesToCheck = [];

        // Recopilar todas las imágenes únicas
        foreach ($items as $item) {
            if ($item->image_small !== 'imagen-no-disponible.jpg' && !isset($imagesToCheck[$item->image_small])) {
                $imagesToCheck[$item->image_small] = [];
            }

            // Agrupar items por imagen
            if ($item->image_small !== 'imagen-no-disponible.jpg') {
                $imagesToCheck[$item->image_small][] = $item->id;
            }
        }

        // Verificar existencia de cada imagen única
        foreach ($imagesToCheck as $imageName => $itemIds) {
            $imagePath = storage_path('app/public/uploads/items/' . $imageName);
            $exists = file_exists($imagePath);

            $validImages[$imageName] = $exists;

            // Si no existe, marcar todos los items que la usan para actualización
            if (!$exists) {
                $itemsToUpdate = array_merge($itemsToUpdate, $itemIds);
            }
        }

        return [
            'itemsToUpdate' => $itemsToUpdate,
            'validImages' => $validImages
        ];
    }

    /**
     * Update missing images in batch with single query
     *
     * @param array $itemIds
     * @return void
     */
    private function updateMissingImagesInBatch($itemIds)
    {
        try {
            // UNA SOLA QUERY para todos los items
            DB::table('items')
                ->whereIn('id', $itemIds)
                ->where('image', '!=', 'imagen-no-disponible.jpg')
                ->update([
                    'image' => 'imagen-no-disponible.jpg',
                    'updated_at' => now()
                ]);

            \Log::info("Auto-corrected missing images for " . count($itemIds) . " items");

        } catch (\Exception $e) {
            \Log::warning("Failed to batch update missing images: " . $e->getMessage());
        }
    }

    /**
     * Get image URL using pre-calculated validation results
     *
     * @param string $imageName
     * @param array $validImages
     * @return string
     */
    private function getImageUrl($imageName, $validImages)
    {
        // Usar la nueva ruta de cache para todas las imágenes
        return route('cached.item.image', ['filename' => $imageName]);
    }

}

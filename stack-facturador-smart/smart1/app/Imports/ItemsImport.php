<?php

namespace App\Imports;

use App\Models\Tenant\Catalogs\UnitType;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Item;
use App\Models\Tenant\ItemWarehouse;
use App\Models\Tenant\Warehouse;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Modules\Item\Models\Category;
use Modules\Item\Models\Brand;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Modules\Inventory\Models\InventoryTransaction;
use Modules\Inventory\Models\Inventory;
use Exception;
use Modules\Item\Models\ItemLotsGroup;
use Modules\Finance\Helpers\UploadFileHelper;
use Modules\Inventory\Models\InventoryConfiguration;

class ItemsImport implements ToCollection
{
    use Importable;

    protected $data;

    public function collection(Collection $rows)
    {
        $configuration = Configuration::first();
        $price_item_007 = $configuration->price_item_007;
        $total = count($rows);
        // $warehouse_id_de = request('warehouse_id');
        $warehouses = Warehouse::all()->transform(function ($item, $index) {
            return [
                'id' => $item->id,
                'description' => $item->description,
                'index' => $index
            ];
        });
        $registered = 0;
        unset($rows[0]);
        foreach ($rows as $index => $row) {

            $description = $row[0];
            $item_type_id = '01';
            $internal_id = ($row[1]) ?: null;
            $model = ($row[2]) ?: null;
            $item_code = ($row[3]) ?: null;
            $unit_type_id = $row[4];
            $currency_type_id = $row[5];
            $sale_unit_price = $row[6];
            $sale_affectation_igv_type_id = $row[7];
            // $has_igv = (strtoupper($row[7]) === 'SI')?true:false;

            $affectation_igv_types_exonerated_unaffected = ['20', '21', '30', '31', '32', '33', '34', '35', '36', '37'];

            if (in_array($sale_affectation_igv_type_id, $affectation_igv_types_exonerated_unaffected)) {

                $has_igv = true;
            } else {

                $has_igv = (strtoupper($row[8]) === 'SI') ? true : false;
            }
            if (!$price_item_007) {
                $min = $has_igv ? 0.07 : 0.06;
                if ($sale_unit_price < $min) {
                    continue;
                }
            }


            $purchase_unit_price = ($row[9]) ?: 0;
            $purchase_affectation_igv_type_id = ($row[10]) ?: null;
            $count_warehouses = count($warehouses);
            $stock_warehouse = [];
            $stock = 0;
            $value = 0;
            $first_warehouse = null;
            foreach ($warehouses as $index => $warehouse) {
                $value = $row[11 + $index] ?? 'vacio'; //11 + 0
                $stock_warehouse[] = $value;
                if ($value !== 'vacio' && $first_warehouse == null) {
                    $first_warehouse = [
                        'id' => $warehouse['id'],
                        'description' => $warehouse['description'],
                        'index' => $index
                    ];
                }
            }
            // Obtener el stock específico del primer almacén
            if ($first_warehouse !== null) {
                $stock = $row[11 + $first_warehouse['index']];
            }

            $stock_min = $row[11 + $count_warehouses];
            $category_name = $row[12 + $count_warehouses];
            $brand_name = $row[13 + $count_warehouses];

            $name = $row[14 + $count_warehouses];
            $second_name = $row[15 + $count_warehouses];

            $lot_code = $row[16 + $count_warehouses];
            $date_of_due = $row[17 + $count_warehouses];
            $barcode = $row[18 + $count_warehouses] ?? null;
            // $image_url = $row[20] ?? null;
            $info_link = $row[19 + $count_warehouses] ?? null;
            $image_url = null;
            // image names
            $file_name = 'imagen-no-disponible.jpg';
            $file_name_medium = 'imagen-no-disponible.jpg';
            $file_name_small = 'imagen-no-disponible.jpg';



            // verifica el campo url y valida si es una url correcta
            if ($image_url && filter_var($image_url, FILTER_VALIDATE_URL)) {
                // verifica si la url no obtiene errores
                if (strpos(get_headers($image_url, 1)[0], '200') != false) {
                    $image_type = exif_imagetype($image_url);
                    // verifica si lo que obtiene de la url es una imagen
                    if ($image_type > 0 || $image_type < 19) {
                        $directory = 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR;
                        $dateNow = date('YmdHis');
                        $content = file_get_contents($image_url);

                        UploadFileHelper::checkIfImageCanBeProcessed($content);

                        $slugs = explode("/", $image_url);
                        $latestSlug = $slugs[(count($slugs) - 1)];
                        $image_name = strtok($latestSlug, '?');

                        $file_name = Str::slug($description) . '-' . $dateNow . '.' . $image_name;
                        $file_name_medium = Str::slug($description) . '-' . $dateNow . '_medium.' . $image_name;
                        $file_name_small = Str::slug($description) . '-' . $dateNow . '_small.' . $image_name;

                        Storage::put($directory . $file_name, $content);

                        $getImage = Storage::get($directory . $file_name);

                        $image_medium = \Image::make($getImage)
                            ->resize(512, null, function ($constraint) {
                                $constraint->aspectRatio();
                                $constraint->upsize();
                            })
                            ->stream();

                        Storage::put($directory . $file_name_medium, $image_medium);

                        $image_small = \Image::make($getImage)
                            ->resize(256, null, function ($constraint) {
                                $constraint->aspectRatio();
                                $constraint->upsize();
                            })
                            ->stream();

                        Storage::put($directory . $file_name_small, $image_small);
                    }
                }
            }
            $warehouse_id = $first_warehouse['id'];
            $warehouse_id_de = $warehouse_id;

            if ($internal_id) {
                $item = Item::where('internal_id', $internal_id)
                    ->first();
            } else {
                $item = null;
            }
            // $establishment_id = auth()->user()->establishment->id;

            if (!$item) {
                $category = $category_name ? Category::updateOrCreate(['name' => $category_name]) : null;
                $brand = $brand_name ? Brand::updateOrCreate(['name' => $brand_name]) : null;


                if ($lot_code && $date_of_due) {
                    try {
                        $_date_of_due = Date::excelToDateTimeObject($date_of_due)->format('Y-m-d');
                    } catch (\Exception $e) {
                        throw new Exception("Debe ingresar el Fecha de vencimiento para el Lote, fecha: " . $row[18] . " en la fila: " . $index + 1, 500);
                    }


                    $new_item = Item::create([
                        'info_link' => $info_link,
                        'description' => $description,
                        'model' => $model,
                        'item_type_id' => $item_type_id,
                        'internal_id' => $internal_id,
                        'item_code' => $item_code,
                        'unit_type_id' => $unit_type_id,
                        'currency_type_id' => $currency_type_id,
                        'sale_unit_price' => $sale_unit_price,
                        'sale_affectation_igv_type_id' => $sale_affectation_igv_type_id,
                        'has_igv' => $has_igv,
                        'purchase_unit_price' => $purchase_unit_price,
                        'purchase_affectation_igv_type_id' => $purchase_affectation_igv_type_id,
                        'stock' => $stock,
                        'stock_min' => $stock_min,
                        'category_id' => optional($category)->id,
                        'brand_id' => optional($brand)->id,
                        'name' => $name,
                        'second_name' => $second_name,

                        'lots_enabled' => true,
                        'lot_code' => $lot_code,
                        'date_of_due' => $_date_of_due,
                        'barcode' => $barcode,
                        'warehouse_id' => $warehouse_id,
                        'image' => $file_name,
                        'image_medium' => $file_name_medium,
                        'image_small' => $file_name_small,
                    ]);

                    $new_item->lots_group()->create([
                        'code'  => $lot_code,
                        'quantity'  => $stock,
                        'date_of_due'  => $_date_of_due,
                        'warehouse_id' => $warehouse_id,

                    ]);
                } else {
                    if ($date_of_due) {
                        try {
                            $_date_of_due = Date::excelToDateTimeObject($date_of_due)->format('Y-m-d');
                        } catch (\Exception $e) {
                            throw new Exception("La fecha de vencimiento no es valida" . $index + 1, 500);
                        }
                    }
                    $new_item = Item::create([
                        'info_link' => $info_link,
                        'description' => $description,
                        'model' => $model,
                        'item_type_id' => $item_type_id,
                        'internal_id' => $internal_id,
                        'item_code' => $item_code,
                        'unit_type_id' => $unit_type_id,
                        'currency_type_id' => $currency_type_id,
                        'sale_unit_price' => $sale_unit_price,
                        'sale_affectation_igv_type_id' => $sale_affectation_igv_type_id,
                        'has_igv' => $has_igv,
                        'purchase_unit_price' => $purchase_unit_price,
                        'purchase_affectation_igv_type_id' => $purchase_affectation_igv_type_id,
                        'stock' => $stock,
                        'stock_min' => $stock_min,
                        'category_id' => optional($category)->id,
                        'brand_id' => optional($brand)->id,
                        'name' => $name,
                        'second_name' => $second_name,
                        'barcode' => $barcode,
                        'warehouse_id' => $warehouse_id,
                        'image' => $file_name,
                        'image_medium' => $file_name_medium,
                        'image_small' => $file_name_small,
                        'date_of_due' => $date_of_due ? $_date_of_due : null,
                    ]);
                }
                if ($new_item && $new_item instanceof Item) {
                    $this->generateInternalId($new_item);
                }
                $item_id = $new_item->id;
                foreach ($warehouses as $index => $warehouse) {
                    $value = $row[11 + $index];
                    if ($value !== null) {
                        $value = floatval($value);
                    } else {
                        $value = 'vacio';
                    }
                    $warehouse_id = $warehouse['id'];

                    if ($value !== "vacio") {

                        $exists = ItemWarehouse::where([
                            'item_id' => $item_id,
                            'warehouse_id' => $warehouse_id
                        ])->first();

                        if ($exists) {

                            if ($value > 0) {
                                $exists->stock = $value;
                                $exists->save();
                            }
                        } else {

                            if ($index !== 0 && $value > 0) {
                                $inventory_transaction = InventoryTransaction::findOrFail('102');
                                $inventory = new Inventory();
                                $inventory->type = null;
                                $inventory->description = $inventory_transaction->name;
                                $inventory->item_id = $item_id;
                                $inventory->warehouse_id = $warehouse_id;
                                $inventory->quantity = $value;
                                $inventory->inventory_transaction_id = $inventory_transaction->id;
                                $inventory->lot_code = $lot_code;
                                $inventory->save();
                            } else if ($value == 0) {
                                ItemWarehouse::create([
                                    'item_id' => $item_id,
                                    'warehouse_id' => $warehouse_id,
                                    'stock' => 0
                                ]);
                            }
                        }
                    }
                }
                $registered += 1;
            } else {
                $category = $category_name ? Category::updateOrCreate(['name' => $category_name]) : null;
                $brand = $brand_name ? Brand::updateOrCreate(['name' => $brand_name]) : null;
                $inventory_transaction = InventoryTransaction::findOrFail('102');

                if ($stock === null) {
                    // throw new Exception("Debe ingresar el stock, internal_id: " . $internal_id , 500);
                    $stock = 0;
                }
                $date_of_due = $row[17 + $count_warehouses];
                if ($date_of_due) {
                    try {
                        $_date_of_due = Date::excelToDateTimeObject($date_of_due)->format('Y-m-d');
                    } catch (\Exception $e) {
                        throw new Exception("La fecha de vencimiento no es valida" . $index + 1, 500);
                    }
                }
                // Procesar todos los almacenes por igual
                $total_stock = 0;
                
                foreach ($warehouses as $index => $warehouse) {
                    $warehouse_id_current = $warehouse['id'];
                    $value = $row[11 + $index] ?? 'vacio';
                    
                    if ($value !== 'vacio' && $value !== null) {
                        $value = floatval($value);
                        $total_stock += $value;
                        
                        $current_stock = ItemWarehouse::where('item_id', $item->id)
                            ->where('warehouse_id', $warehouse_id_current)
                            ->value('stock') ?? 0;

                        $stock_difference = $value - $current_stock;

                        // Crear ajuste si hay diferencia
                        if ($stock_difference !== 0) {
                            // Solo crear movimiento si existe registro previo O si el nuevo valor no es 0
                            if ($current_stock != 0 || $value != 0) {
                                if ($stock_difference > 0) {
                                    $type = 1; // Entrada
                                    $inventory_transaction_id = '102';
                                    $quantity_adjustment = $stock_difference;
                                } else {
                                    $type = 3; // Salida
                                    $inventory_transaction_id = '28';
                                    $quantity_adjustment = abs($stock_difference);
                                }

                                $inventory = new Inventory();
                                $inventory->type = $type;
                                $inventory->description = 'Ajuste de Stock';
                                $inventory->item_id = $item->id;
                                $inventory->warehouse_id = $warehouse_id_current;
                                $inventory->quantity = $quantity_adjustment;
                                $inventory->inventory_transaction_id = $inventory_transaction_id;
                                $inventory->save();
                            }
                        }

                        // Actualizar/crear ItemWarehouse
                        ItemWarehouse::updateOrCreate(
                            [
                                'item_id' => $item->id,
                                'warehouse_id' => $warehouse_id_current
                            ],
                            [
                                'stock' => $value
                            ]
                        );
                    }
                }

                // Actualizar item con stock total y otros datos
                $item->update([
                    'date_of_due' => $date_of_due ? $_date_of_due : null,
                    'info_link' => $info_link,
                    'description' => $description,
                    'model' => $model,
                    'item_type_id' => $item_type_id,
                    'internal_id' => $internal_id,
                    'item_code' => $item_code,
                    'unit_type_id' => $unit_type_id,
                    'currency_type_id' => $currency_type_id,
                    'sale_unit_price' => $sale_unit_price,
                    'sale_affectation_igv_type_id' => $sale_affectation_igv_type_id,
                    'has_igv' => $has_igv,
                    'purchase_unit_price' => $purchase_unit_price,
                    'purchase_affectation_igv_type_id' => $purchase_affectation_igv_type_id,
                    'stock' => $total_stock,
                    'stock_min' => $stock_min,
                    'name' => $name,
                    'second_name' => $second_name,
                    'barcode' => $barcode,
                    'category_id' => optional($category)->id,
                    'brand_id' => optional($brand)->id,
                ]);


                $lot_code = $row[16 + $count_warehouses];
                if ($lot_code && $total_stock > 0) {

                    if (!$date_of_due) {
                        throw new Exception("Debe ingresar el Fecha de vencimiento para el Lote", 500);
                    }

                    $current_lot = ItemLotsGroup::where([
                        'code' => $lot_code,
                        'item_id' => $item->id
                    ])->first();

                    if ($current_lot) {
                        $current_lot->quantity = (int)$total_stock;
                        $current_lot->old_quantity = $current_lot->quantity;
                        $current_lot->save();
                    } else {
                        try {
                            $_date_of_due = Date::excelToDateTimeObject($date_of_due)->format('Y-m-d');
                        } catch (\Exception $e) {
                            throw new Exception("Debe ingresar el Fecha de vencimiento para el Lote, fecha: " . $row[18] . " en la fila: " . $index + 1, 500);
                        }

                        ItemLotsGroup::create([
                            'code' => $lot_code,
                            'quantity' => $total_stock,
                            'old_quantity' => $total_stock,
                            'date_of_due' =>  $_date_of_due,
                            'item_id' => $item->id,
                            'warehouse_id' => $warehouse_id,
                        ]);
                    }
                }



                $registered += 1;
            }
        }
        $this->data = compact('total', 'registered', 'warehouse_id_de');
    }

    public function getData()
    {
        return $this->data;
    }

    public function generateInternalId(Item &$item)
    {
        $inventory_configuration = InventoryConfiguration::select('generate_internal_id')->firstOrFail();

        if ($inventory_configuration->generate_internal_id && !$item->internal_id) {
            $item->internal_id = str_pad($item->id, 5, '0', STR_PAD_LEFT);
            $item->save();
        }
    }
}

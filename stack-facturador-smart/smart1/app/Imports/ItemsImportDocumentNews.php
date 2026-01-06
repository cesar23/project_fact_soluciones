<?php

namespace App\Imports;

use App\Http\Controllers\SearchItemController;
use App\Models\Tenant\Catalogs\UnitType;
use App\Models\Tenant\Item;
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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Item\Models\ItemLotsGroup;
use Modules\Finance\Helpers\UploadFileHelper;


class ItemsImportDocumentNews implements ToCollection
{
    use Importable;

    protected $data;

    public function collection(Collection $rows)
    {
        $total = count($rows);
        // $warehouse_id_de = request('warehouse_id');
        $establishment_id = auth()->user()->establishment->id;
        $warehouse_id_de = Warehouse::where('establishment_id', $establishment_id)->first()->id;
        $registered = 0;
        $errors = [];
        $items = [];
        $type = request('type');
        unset($rows[0]);
        foreach ($rows as $row) {
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

            $purchase_unit_price = ($row[9]) ?: 0;
            $purchase_affectation_igv_type_id = ($row[10]) ?: null;
            $stock = $row[11];
            $stock_min = $row[12];
            $category_name = $row[13];
            $brand_name = $row[14];

            $name = $row[15];
            $second_name = $row[16];

            $lot_code = $row[17];
            $date_of_due = $row[18];
            $barcode = $row[19] ?? null;
            // $image_url = $row[20] ?? null;
            $info_link = $row[20] ?? null;
            $quantity = $row[21] ?? 1;
            $image_url = null;
            // image names
            $file_name = 'imagen-no-disponible.jpg';
            $file_name_medium = 'imagen-no-disponible.jpg';
            $file_name_small = 'imagen-no-disponible.jpg';



            // verifica el campo url y valida si es una url correcta
            if ($image_url && filter_var($image_url, FILTER_VALIDATE_URL)) {
                // verifica si la url no obtiene errores
                if (strpos(get_headers($image_url, 1)[0], '200') != false) {
                    // dd(stripos(getimagesize($image_url)['mime'], 'image') === 0? 'no':'si');
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

            $warehouse_id = request('warehouse_id');


            if ($internal_id) {
                $item = Item::where('internal_id', $internal_id)
                    ->first();
            } else {
                $item = null;
            }
            // $establishment_id = auth()->user()->establishment->id;

            if ($sale_unit_price > 0) {
                if (!$item) {
                    $category = $category_name ? Category::updateOrCreate(['name' => $category_name]) : null;
                    $brand = $brand_name ? Brand::updateOrCreate(['name' => $brand_name]) : null;

                    // dd($row, $lot_code ,$date_of_due, $category, $brand);

                    if ($lot_code && $date_of_due) {

                        $_date_of_due = Date::excelToDateTimeObject($date_of_due)->format('Y-m-d');

                        // dd($lot_code, $date_of_due, $x);

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
                        ]);
                    } else {

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
                        ]);
                    }
                    //      $new_item
                    switch ($type) {
                        case 'quotations':
                            # code...
                            $found =   (new SearchItemController)->getItemsToQuotation(null, $new_item->id);
                            break;
                        case 'purchases':
                            # code...
                            $found =   (new SearchItemController)->getItemToPurchase(null, $new_item->id);
                            break;
                        default:
                            # code...
                            $found =   (new SearchItemController)->getItemsToDocuments(null, $new_item->id);
                            break;
                    }
                    // $found  =  (new SearchItemController)->getItemsToDocuments(null, $new_item->id);
                    $found = isset($found[0]) ? $found[0] : null;
                    //cambiar sale_unit_price de $found por 18
                    $found["sale_unit_price"] = $sale_unit_price;
                    if ($type == 'purchases') {
                        $found["purchase_unit_price"] =  $sale_unit_price;
                    }
                    $found["quantity"] = $quantity;
                    // $found["quantity"] = 1;
                    if ($found) {
                        $items[] = $found;
                    }
                    $registered += 1;
                }
            }
        }
        $this->data = compact('total', 'registered', 'warehouse_id_de', 'items');
    }

    public function getData()
    {
        return $this->data;
    }
}

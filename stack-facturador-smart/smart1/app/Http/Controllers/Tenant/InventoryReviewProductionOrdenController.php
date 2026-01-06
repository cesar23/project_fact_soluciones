<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Inventory\Models\{
    Warehouse,
    ItemWarehouse,
};
use Modules\Item\Models\{
    Category,
};
use App\Models\Tenant\{
    Establishment,
    CatItemSize,
    Company,
    ProductionOrder,
};
use App\Models\Tenant\Catalogs\{
    CatColorsItem
};
use App\Exports\GeneralFormatExport;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\InventoryReviewProductionOrdenExport;
use App\Http\Resources\Tenant\InventoryReviewProductionOrdenCollection;
use Illuminate\Support\Facades\DB;

class InventoryReviewProductionOrdenController extends Controller
{

    public function index()
    {
        return view('tenant.inventory_review_production_orden.index');
    }


    public function filters()
    {
        $warehouses = Warehouse::getDataForFilters();
        $categories = Category::getDataForFilters();
        $item_sizes = CatItemSize::get();
        $item_colors = CatColorsItem::get();

        return compact('warehouses', 'categories', 'item_sizes', 'item_colors');
    }

    public function downloadPdf(Request $request)
    {
        $request->merge(['only_picking' => true]);
        $records = $this->getRecordsPaginate($request)->get();
        $items_id = $records->pluck('item_id');
        $production_order = DB::connection('tenant')
            ->table('production_orders as po')
            ->select('po.id', 'po.number', 'poi.item_id', 'poi.quantity', 'poi.item')
            ->join('production_order_items as poi', 'po.id', '=', 'poi.production_order_id')
            ->whereIn('poi.item_id', $items_id)
            ->where('po.production_order_state_id', 2)
            ->get()
            ->map(function ($item) {
                // Decodificar el JSON de la columna 'item'
                $item->item = json_decode($item->item);
                // Crear una clave de agrupación basada en item_id + presentación
                $presentation_key = isset($item->item->presentation->id) ? $item->item->presentation->id : 'sin_presentacion';
                $item->group_key = $item->item_id . '_' . $presentation_key;
                return $item;
            })
            ->groupBy('group_key');
        
        $grouped_records = collect();
        
        foreach ($records as $row) {
            $item_id = $row->item_id;
            
            // Obtener todos los grupos de órdenes de producción para este item
            $item_production_orders = $production_order->filter(function($group, $key) use ($item_id) {
                return str_starts_with($key, $item_id . '_');
            });
            
            // Crear un registro por cada grupo (item + presentación)
            foreach ($item_production_orders as $group_key => $production_order_items) {
                $count_production_order = $production_order_items->count();
                $number_production_order = $production_order_items->pluck('number');

                // Calcular cantidad total SIN considerar la presentación (ya que se está detallando)
                $total_quantity = $production_order_items->sum('quantity');

                // Obtener la descripción de la presentación
                $production_order_item = $production_order_items->first();
                $presentation_item = null;

                if ($production_order_item && isset($production_order_item->item->presentation)) {
                    $presentation_item = $production_order_item->item->presentation;
                }

                $description_item = $row->item->description;
                if ($presentation_item && isset($presentation_item->description)) {
                    $description_item .= " " . $presentation_item->description;
                }
                
                $item_fulldescription = "";
                if ($row->item->internal_id) {
                    $item_fulldescription = "{$row->item->internal_id} - {$description_item}";
                }

                $grouped_records->push([
                    'id' => $row->id,
                    'item_id' => $row->item_id,
                    'item_fulldescription' => $item_fulldescription,
                    'total_quantity' => $total_quantity,
                    'count_production_order' => $count_production_order,
                    'production_orders_number' => $number_production_order,
                ]);
            }
        }
        
        $records = $grouped_records;
        $company = Company::active();
        $warehouse_id = $request->warehouse_id;
        if ($warehouse_id) {
            $warehouse = Warehouse::find($warehouse_id);
            $establishment = $warehouse->establishment;
        } else {
            $establishment = Establishment::find(auth()->user()->establishment_id);
        }
        $pdf = Pdf::loadView('tenant.inventory_review_production_orden.exports.report_pdf', compact(
            'records',
            'company',
            'establishment'
        ))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Reporte_inventario_' . Carbon::now() . '.pdf');
    }
    public function downloadPdfLandscape(Request $request)
    {
        $request->merge(['only_picking' => true]);
        $records = $this->getRecordsPaginate($request)->get();
        $items_id = $records->pluck('item_id');
        $production_order = DB::connection('tenant')
            ->table('production_orders as po')
            ->select('po.id', 'po.number', 'poi.item_id', 'poi.quantity', 'poi.item')
            ->join('production_order_items as poi', 'po.id', '=', 'poi.production_order_id')
            ->whereIn('poi.item_id', $items_id)
            ->where('po.production_order_state_id', 2)
            ->get()
            ->map(function ($item) {
                // Decodificar el JSON de la columna 'item'
                $item->item = json_decode($item->item);
                return $item;
            })
            ->groupBy('item_id');
        $records = $records->transform(function ($row, $index) use ($production_order) {
            $production_order_items = isset($production_order[$row->item_id]) ? $production_order[$row->item_id] : collect();
            $count_production_order = $production_order_items->count();
            $production_orders_number = $production_order_items->pluck('number');

            // Calcular cantidad total considerando la presentación de cada registro individualmente
            $total_quantity = 0;
            foreach ($production_order_items as $po_item) {
                $item_quantity = $po_item->quantity;

                // Si tiene presentación con quantity_unit, multiplicar por cada registro
                if (isset($po_item->item->presentation->quantity_unit)) {
                    $item_quantity = $item_quantity * $po_item->item->presentation->quantity_unit;
                }

                $total_quantity += $item_quantity;
            }

            return [
                'id' => $row->id,
                'item_id' => $row->item_id,
                'item_internal_id' => $row->item->internal_id,
                'item_description' => $row->item->description,
                'production_orders_number' => $production_orders_number,
                'item_fulldescription' => ($row->item->internal_id) ? "{$row->item->internal_id} - {$row->item->description}" : $row->item->description,
                'warehouse_description' => $row->warehouse->description,
                'stock' => $row->stock,
                'count_production_order' => $count_production_order,
                'total_quantity' => $total_quantity,
                'requirement' => $row->stock - $total_quantity,
                'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $row->updated_at->format('Y-m-d H:i:s'),
            ];
        });
        $company = Company::active();

        $warehouse_id = $request->warehouse_id;
        if ($warehouse_id) {
            $warehouse = Warehouse::find($warehouse_id);
            $establishment = $warehouse->establishment;
        } else {
            $establishment = Establishment::find(auth()->user()->establishment_id);
        }
        $pdf = Pdf::loadView('tenant.inventory_review_production_orden.exports.report_pdf_landscape', compact(
            'records',
            'company',
            'establishment'
        ))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('Reporte_inventario_' . Carbon::now() . '.pdf');
    }
    public function downloadPdfTicket(Request $request)
    {
        $records = $this->getRecordsPaginate($request)->get();
        $records = $records->transform(function ($row, $index) {
            return [
                'id' => $row->id,
                'item_id' => $row->item_id,
                'item_internal_id' => $row->item->internal_id,
                'item_description' => $row->item->description,
                'item_fulldescription' => ($row->item->internal_id) ? "{$row->item->internal_id} - {$row->item->description}" : $row->item->description,
                'warehouse_description' => $row->warehouse->description,
                'stock' => $row->stock,
                'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $row->updated_at->format('Y-m-d H:i:s'),
            ];
        });
        $company = Company::active();
        $h_by_record = 50;
        $height = $records->count() * $h_by_record;
        $warehouse_id = $request->warehouse_id;
        $warehouse = Warehouse::find($warehouse_id);
        $establishment = $warehouse->establishment;
        $pdf = Pdf::loadView('tenant.inventory_review_production_orden.exports.report_pdf_ticket', compact(
            'records',
            'company',
            'establishment'
        ))
            ->setPaper(array(0, 0, 249.45, $height), 'portrait');

        return $pdf->stream('Reporte_inventario_' . Carbon::now() . '.pdf');
    }
    public function downloadExcel(Request $request)
    {
        $records = $this->getRecordsPaginate($request)->get();
        $records = $records->transform(function ($row, $index) {
            return [
                'id' => $row->id,
                'item_id' => $row->item_id,
                'item_internal_id' => $row->item->internal_id,
                'item_description' => $row->item->description,
                'item_fulldescription' => ($row->item->internal_id) ? "{$row->item->internal_id} - {$row->item->description}" : $row->item->description,
                'warehouse_description' => $row->warehouse->description,
                'stock' => $row->stock,
                'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $row->updated_at->format('Y-m-d H:i:s'),
            ];
        });
        $company = Company::active();
        $h_by_record = 50;
        $warehouse_id = $request->warehouse_id;
        $warehouse = Warehouse::find($warehouse_id);
        $establishment = $warehouse->establishment;

        return (new InventoryReviewProductionOrdenExport)
            ->records($records)
            ->company($company)
            ->establishment($establishment)
            ->download('Reporte_inventario_' . Carbon::now() . '.xlsx');
    }

    public function getRecordsPaginate(Request $request)
    {
        $filter = $request->filter;
        $only_picking = $request->only_picking;
        $conditions = [
            '01' => [],
            '02' => function ($query) {
                $query->where('stock', '<', 0);
            },
            '03' => ['stock', '=', 0],
            '04' => function ($query) {
                $query->where('stock', '>', 0)
                    ->whereHas('item', function ($q) {
                        $q->whereColumn('item_warehouse.stock', '<=', 'items.stock_min');
                    });
            },
            '05' => function ($query) {
                $query->whereHas('item', function ($q) {
                    $q->whereColumn('item_warehouse.stock', '>', 'items.stock_min');
                });
            },
        ];
        $records = ItemWarehouse::with([
            'item' => function ($q) {
                $q->whereFilterWithOutRelations();
            }
        ])
            ->whereHas('item', function ($query) use ($request, $only_picking) {

                $query->whereIsNotService()->whereNotIsSet()->whereIsActive();

                $category_id = $request->has('category_id') && $request->category_id;
                $item_description = $request->has('item_description') && $request->item_description;
                if ($category_id) $query->where('category_id', $request->category_id);
                if ($item_description) $query->where('description', 'like', "%{$request->item_description}%");
                if ($only_picking) {

                    $query->whereHas('production_order_item', function ($query) use ($request) {
                        $query->whereHas('production_order', function ($query) use ($request) {
                            $query->where('production_order_state_id', 2);
                        });
                    });
                }
                return $query;
            });
            if ($request->warehouse_id) {
                $records->where('warehouse_id', $request->warehouse_id);
            }
        if (isset($conditions[$filter]) && !$only_picking) {
            $condition = $conditions[$filter];
            if (is_callable($condition)) {
                $records->where($condition);
            } elseif (!empty($condition)) {
                $records->where($condition[0], $condition[1], $condition[2]);
            }
        }
    
        $records->orderBy('item_id');

        return $records;
    }
    /**
     *
     * @param  Request $request
     * @return array
     */
    public function recordsPaginate(Request $request)
    {
        $records = $this->getRecordsPaginate($request);

        return new InventoryReviewProductionOrdenCollection($records->paginate(config('tenant.items_per_page')));
    }
    public function recordsPaginateItemIdsPicking(Request $request){
        $request->merge(['only_picking' => true]);
        $records = $this->getRecordsPaginate($request)->get();
        $items_id = $records->pluck('item_id');
        $production_order = DB::connection('tenant')
        ->table('production_orders as po')
        ->select('po.id', 'po.number', 'poi.item_id', 'poi.quantity')
        ->join('production_order_items as poi', 'po.id', '=', 'poi.production_order_id')
        ->whereIn('poi.item_id', $items_id)
        ->where('po.production_order_state_id', 2)
        ->get()
        ->groupBy('item_id');
        $records = $records->transform(function ($row, $index) use ($production_order) {
            $total_quantity = $production_order[$row->item_id]->sum('quantity');
            $requirement = $row->stock - $total_quantity;
            if ($requirement < 0) {
                return [
                    'item_id' => $row->item_id,
                    'requirement' => abs($requirement)
                ];
            }
            return null;
        })->filter();
        return $records;
    }
    public function recordsPaginateItemIds(Request $request)
    {
        $records = $this->getRecordsPaginate($request)->get()->pluck('item_id');

        return $records;
    }
    public function records(Request $request)
    {
        $filter_by_variants = $request->has('filter_by_variants') && $request->filter_by_variants === 'true';

        $records = ItemWarehouse::with([
            'item' => function ($q) {
                $q->whereFilterWithOutRelations();
            }
        ])
            ->whereHas('item', function ($query) use ($request, $filter_by_variants) {

                $query->whereIsNotService()->whereNotIsSet()->whereIsActive();

                $category_id = $request->has('category_id') && $request->category_id;
                if ($category_id) $query->where('category_id', $request->category_id);

                // para variantes
                if ($filter_by_variants) {
                    $query->whereHas('item_movement_rel_extra', function ($query_rel_extra) use ($request) {

                        $item_color_id = $request->item_color_id ?? false;
                        if ($item_color_id) $query_rel_extra->where('item_color_id', $item_color_id);

                        $item_size_id = $request->item_size_id ?? false;
                        if ($item_size_id) $query_rel_extra->where('item_size_id', $item_size_id);

                        return $query_rel_extra;
                    });
                }
                // para variantes

                return $query;
            })
            ->where('warehouse_id', $request->warehouse_id)
            ->orderBy('item_id')
            ->get()
            ->transform(function ($row, $index) use ($filter_by_variants, $request) {
                return [
                    'index' => $index + 1,
                    'id' => $row->id,
                    'item_id' => $row->item_id,
                    'item_description' => $row->item->description,
                    'item_barcode' => $row->item->barcode,
                    'stock' => $row->stock,
                    'input_stock' => 0,
                    'difference' => null,
                    // 'stock_by_variants' => $filter_by_variants ? $row->item->getStockByVariantsInventoryReview($request->establishment_id) : null,
                    'stock_by_variants' => null,
                ];
            });


        if ($filter_by_variants) {
            $records = $this->transformDataForVariants($records, $request);
        }

        return [
            'data' => $records
        ];
    }
    /**
     *
     * Transformar datos de los items encontrados para las variantes
     *
     * @param  array $records
     * @param  Request $request
     * @return array
     */
    private function transformDataForVariants($records, $request)
    {
        $data = [];
        $index = 0;

        $records->each(function ($row) use (&$data, $index, $request) {

            $colors = $row['stock_by_variants']['colors'] ?? null;
            $sizes = $row['stock_by_variants']['CatItemSize'] ?? null;

            if ($colors) {
                if ($this->isSetDataVariant($request) || ($request->item_color_id && !$request->item_size_id)) {
                    $this->setDataToVariant($colors['detailed'], $row, $data, $index, 'Color');
                }
            }

            if ($sizes) {
                if ($this->isSetDataVariant($request) || (!$request->item_color_id && $request->item_size_id)) {
                    $this->setDataToVariant($sizes['detailed'], $row, $data, $index, 'Talla');
                }
            }
        });

        return $data;
    }


    /**
     *
     * @param  Request $request
     * @return bool
     */
    private function isSetDataVariant($request)
    {
        return ($request->item_color_id && $request->item_size_id) || (!$request->item_color_id && !$request->item_size_id);
    }


    /**
     *
     * Asignar datos de las variante
     *
     * @param  array $data_detailed
     * @param  array $row
     * @param  array $data
     * @param  int $index
     * @return void
     */
    private function setDataToVariant($data_detailed, $row, &$data, &$index, $type)
    {
        if ($data_detailed->count() > 0) {
            foreach ($data_detailed as $value) {
                $data[] = [
                    'index' => $index + 1,
                    'id' => $row['id'],
                    'item_id' => $row['item_id'],
                    'item_description' => $row['item_description'] . " - {$type}: " . $value->name,
                    'item_barcode' => $row['item_barcode'],
                    'stock' => (float) $value->total,
                    'input_stock' => 0,
                    'difference' => null,
                    'stock_by_variants' => null,
                ];
                $index++;
            }
        }
    }


    /**
     *
     * Exportar formato pdf/excel
     *
     * @param  Request $request
     * @return mixed
     */
    public function export(Request $request)
    {
        $this->initConfigurations();
        $format = $request->format;
        $records = $request->records;
        $company = Company::getDataForReportHeader();
        $data = compact('records', 'company');
        $view = 'tenant.inventory_review_production_orden.exports.general_format';
        $filename = 'Revision_stock_' . Carbon::now() . ".{$format}";

        if ($format === 'pdf') {
            $pdf = PDF::loadView($view, $data);
            $export = $pdf->download($filename);
        } else {
            $general_format_export = new GeneralFormatExport();
            $general_format_export->data($data)->view_name($view);
            $export = $general_format_export->download($filename);
        }

        return $export;
    }


    /**
     *
     * @return void
     */
    private function initConfigurations()
    {
        ini_set('memory_limit', '4026M');
        ini_set("pcre.backtrack_limit", "5000000");
    }
}

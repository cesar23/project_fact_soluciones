<?php

namespace Modules\Inventory\Http\Controllers;

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
};
use App\Models\Tenant\Catalogs\{
    CatColorsItem
};
use App\Exports\GeneralFormatExport;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Inventory\Exports\InventoryReviewExport;
use Modules\Inventory\Http\Resources\InventoryReviewCollection;

class InventoryReviewController extends Controller
{

    public function index()
    {
        return view('inventory::inventory-review.index');
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
        $warehouse_id = $request->warehouse_id;
        $warehouse = Warehouse::find($warehouse_id);
        $establishment = $warehouse->establishment;
        $pdf = Pdf::loadView('inventory::inventory-review.exports.report_pdf', compact(
            'records',
            'company',
            'establishment'
        ))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Reporte_inventario_' . Carbon::now() . '.pdf');
    }
    public function downloadPdfLandscape(Request $request)
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
        $warehouse_id = $request->warehouse_id;
        $warehouse = Warehouse::find($warehouse_id);
        $establishment = $warehouse->establishment;
        $pdf = Pdf::loadView('inventory::inventory-review.exports.report_pdf', compact(
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
        $pdf = Pdf::loadView('inventory::inventory-review.exports.report_pdf_ticket', compact(
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

        return (new InventoryReviewExport)
        ->records($records)
        ->company($company)
        ->establishment($establishment)
        ->download('Reporte_inventario_' . Carbon::now() . '.xlsx');

    }

    public function getRecordsPaginate(Request $request)
    {
        $filter = $request->filter;
        $conditions = [
            '01' => [], // Todos
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
            ->whereHas('item', function ($query) use ($request) {

                $query->whereIsNotService()->whereNotIsSet()->whereIsActive();

                $category_id = $request->has('category_id') && $request->category_id;
                $item_description = $request->has('item_description') && $request->item_description;
                if ($category_id) $query->where('category_id', $request->category_id);
                if ($item_description) $query->where('description', 'like', "%{$request->item_description}%");
                return $query;
            })
            ->where('warehouse_id', $request->warehouse_id);
        if (isset($conditions[$filter])) {
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

        return new InventoryReviewCollection($records->paginate(config('tenant.items_per_page')));
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
        $view = 'inventory::inventory-review.exports.general_format';
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

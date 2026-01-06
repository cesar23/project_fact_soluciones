<?php

namespace Modules\Report\Http\Controllers;

use App\Models\Tenant\Catalogs\DocumentType;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Company;
use App\Models\Tenant\Item;
use App\Models\Tenant\Person;
use App\Models\Tenant\PersonType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Report\Exports\OrderNoteConsolidatedTotalExport;
use Modules\Report\Http\Resources\OrderNoteConsolidatedCollection;
use Modules\Report\Traits\ReportTrait;
use Modules\Order\Models\OrderNoteItem;
use Modules\Report\Http\Resources\OrderNoteVariableCollection;
use Modules\Report\Exports\OrderNoteVariableExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportOrderNoteVariableController extends Controller
{
    use ReportTrait;

    public function filter()
    {


        $persons = $this->getPersons('customers');
        $date_range_types = $this->getDateRangeTypes();
        $order_state_types = $this->getOrderStateTypes();
        $sellers = $this->getSellers();

        return compact('persons', 'date_range_types', 'order_state_types', 'sellers');
    }


    public function search($columnKey, Request $request)
    {

        $records = collect();
        if ($columnKey == 'customer_name' ||  $columnKey == 'customer_number') {
            $records = Person::select('id', 'name', 'number')->where('name', 'like', '%' . $request->search . '%')

                ->orWhere('number', 'like', '%' . $request->search . '%')
                ->limit(10)
                ->get()->transform(function ($row) {
                    return [
                        'id' => $row->id,
                        'name' => $row->name,
                        'number' => $row->number
                    ];
                });
        } else if ($columnKey == 'item_description') {
            $records = Item::select('id', 'description')->where('description', 'like', '%' . $request->search . '%')->limit(10)->get()->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'description' => $row->description
                ];
            });
        } 
        
        else if ($columnKey == 'person_type') {
            $records = PersonType::select('id', 'description')->where('description', 'like', '%' . $request->search . '%')->limit(10)->get()->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'description' => $row->description
                ];
            });
        } else {
            $records = OrderNoteItem::where($columnKey, 'like', '%' . $request->search . '%')->get();
        }


        return response()->json($records);
    }

    public function index()
    {


        return view('report::order_notes_variable.index');
    }

    public function records(Request $request)
    {
        $per_page = $request->per_page ?? config('tenant.items_per_page');
        $records = $this->getRecordsOrderNotes($request->all(), OrderNoteItem::class);

        return new OrderNoteVariableCollection($records->paginate($per_page));
    }


    /**
     * @param array                $request
     * @param OrderNoteItem::class $model
     *
     * @return $this|\Illuminate\Database\Query\Builder
     */
    public function getRecordsOrderNotes($request,  $model)
    {

        // dd($request);

        $records = $this->dataOrderNotes($request, $model);

        return $records;
    }


    /**
     * @param array         $request
     * @param OrderNoteItem $model
     *
     * @return mixed
     */
    // private function dataOrderNotes($request, $model)
    // {
    //     $columns = $request['columns'] ?? "";
    //     $columns = explode(',', $columns);
    //     $first_column = $columns[0];
    //     $items_id = $request['items_id'] ?? null;

    //     $customers_id = $request['customers_id'] ?? null;
    //     $items_id = !is_null($items_id) ? explode(',', $items_id) : [];
    //     $person_type = $request['person_type_ids'] ?? null;
    //     $customers_id = !is_null($customers_id) ? explode(',', $customers_id) : [];
    //     $date_end = $request['date_end'] ?? now();
    //     $date_start = $request['date_start'] ?? now()->subDays(30);

    //     $data = OrderNoteItem::query()
    //         ->join('order_notes', 'order_notes.id', '=', 'order_note_items.order_note_id')
    //         ->join('persons', 'persons.id', '=', 'order_notes.customer_id')
    //         ->join('items', 'items.id', '=', 'order_note_items.item_id')
    //         ->leftJoin('person_types', 'person_types.id', '=', 'persons.person_type_id')
    //         ->whereBetween('order_notes.date_of_issue', [$date_start, $date_end]);

    //     if ($items_id) {
    //         $data->whereIn('order_note_items.item_id', $items_id);
    //     }

    //     if ($customers_id) {
    //         $data->whereIn('order_notes.customer_id', $customers_id);
    //     }

    //     if ($person_type) {
    //         $data->where('persons.person_type_id', $person_type);
    //     }

    //     // Seleccionar todas las columnas necesarias sin agrupar
    //     $selectColumns = [
    //         'person_types.description as person_type',
    //         'persons.number as customer_number',
    //         'persons.name as customer_name',
    //         'items.description as item_description',
    //         'order_notes.date_of_issue as delivery_date',
    //         'order_notes.created_at as created_time',
    //         'order_note_items.quantity as item_quantity',
    //         'order_note_items.unit_price',
    //         'order_note_items.total',
    //         'order_note_items.id' // Incluir ID para unicidad
    //     ];
    //     $data->select($selectColumns)
    //         ->orderBy($first_column);

    //     return $data;
    // }
    private function dataOrderNotes($request, $model)
    {
        $columns = $request['columns'] ?? "";
        $columns = explode(',', $columns);
        $first_column = $columns[0];
        $items_id = $request['items_id'] ?? null;
        $person_type = $request['person_type_ids'] ?? null;
        $customers_id = $request['customers_id'] ?? null;
        $items_id = !is_null($items_id) ? explode(',', $items_id) : [];
        $customers_id = !is_null($customers_id) ? explode(',', $customers_id) : [];
        $date_end = $request['date_end'] ?? now();
        $date_start = $request['date_start'] ?? now()->subDays(30);

        $data = OrderNoteItem::query()
            ->join('order_notes', 'order_notes.id', '=', 'order_note_items.order_note_id')
            ->join('persons', 'persons.id', '=', 'order_notes.customer_id')
            ->join('items', 'items.id', '=', 'order_note_items.item_id')
            ->leftJoin('person_types', 'person_types.id', '=', 'persons.person_type_id')
            ->whereBetween('order_notes.date_of_issue', [$date_start, $date_end]);

        if ($items_id) {
            $data->whereIn('order_note_items.item_id', $items_id);
        }

        if ($customers_id) {
            $data->whereIn('order_notes.customer_id', $customers_id);
        }
        if ($person_type) {
            $data->where('persons.person_type_id', $person_type);
        }

        // Columnas base para agrupar
        $groupByColumns = [
            'person_types.description as person_type',
            'order_notes.customer_id',
            'persons.number as customer_number',
            'persons.name as customer_name',
            'order_note_items.item_id',
            'order_note_items.unit_prince',
            'items.description as item_description',
            'order_notes.date_of_issue as delivery_date',
            'order_notes.created_at as created_time'
        ];

        // Filtrar las columnas que están presentes en la solicitud
        $selectedGroupByColumns = array_filter($groupByColumns, function ($column) use ($columns) {
            $columnKey = explode(' as ', $column)[1] ?? $column;
            return in_array($columnKey, $columns);
        });

        // Si no hay columnas seleccionadas, agrupamos por cliente y producto por defecto
        if (empty($selectedGroupByColumns)) {
            $selectedGroupByColumns = [
                'person_types.description as person_type',
                'order_notes.customer_id',
                'persons.number as customer_number',
                'persons.name as customer_name',
                'order_note_items.item_id',
                'items.description as item_description'
            ];
        }

        // Optimizar el SELECT agregando índices en las columnas de agrupación
        $selectColumns = array_merge($selectedGroupByColumns, [
            'SUM(order_note_items.quantity) as quantity',
            'SUM(order_note_items.total) as total',
            'MAX(order_note_items.unit_price) as unit_price',
            'MIN(order_notes.customer_id) as customer_id'
        ]);

        // Usar índices en el GROUP BY
        $data->selectRaw(implode(', ', $selectColumns))
            ->groupBy(array_map(function ($col) {
                return explode(' as ', $col)[0] ?? $col;
            }, $selectedGroupByColumns))
            ->orderBy($first_column);

        return $data;
    }


    

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Support\Collection
     */
    public function totalsByItem(Request $request)
    {

        // dd($request->all());
        //$records = $this->getRecordsSalesConsolidated($request->all())->get()->groupBy('item_id');
        $records = $this->getRecordsOrderNotes($request->all(), OrderNoteItem::class)->get()->groupBy('item_id');

        return $records->map(function ($row, $key) {
            /**
             * @var \Illuminate\Database\Eloquent\Collection $row
             * @var \Modules\Order\Models\OrderNoteItem $item
             */
            $item = $row->first();
            return [
                'item_id'           => $key,
                'item_internal_id'  => $item->relation_item->internal_id,
                'item_unit_type_id' => $item->relation_item->unit_type_id,
                'item_description'  => $item->item->description,
                'quantity'          => number_format($row->sum('quantity'), 4, '.', ''),
            ];
        });
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function pdfTotals(Request $request)
    {

        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id)
            : auth()->user()->establishment;
        $records = $this->totalsByItem($request)->sortBy('item_id');
        $params = $request->all();
        $pdf = PDF::loadView(
            'report::order_notes_consolidated.report_pdf_totals',
            compact('records', 'company', 'establishment', 'params')
        );
        $filename = 'Reporte_Consolidado_Items_Pedidos_Totales_' . date('YmdHis');

        return $pdf->download($filename . '.pdf');
    }


    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function excelTotals(Request $request)
    {

        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id)
            : auth()->user()->establishment;
        $records = $this->totalsByItem($request)->sortBy('item_id');
        $params = $request->all();
        $filename = 'Reporte_Consolidado_Items_Pedidos_Totales_' . date('YmdHis');
        $SaleConsolidatedTotalExport = new OrderNoteConsolidatedTotalExport();
        $SaleConsolidatedTotalExport->setRecords($records)
            ->setCompany($company)
            ->setEstablishment($establishment)
            ->setParams($params);

        return $SaleConsolidatedTotalExport->download($filename . '.xlsx');
    }

    

    private function formatDate($full_date = null){
        if($full_date){
            $explode_date = explode(' ', $full_date);
            return $explode_date[1];
        }
        return '-';
    }
    public function getTotals(Request $request)
    {
        $totals = $this->getTotalColumns($request, OrderNoteItem::class);
        return response()->json($totals);
    }
    private function getTotalColumns($request, $model)
    {
        $columns = $request['columns'] ?? [];
        $columns = explode(',', $columns);
        
        // Verificar si necesitamos calcular totales
        $hasQuantity = in_array('item_quantity', $columns);
        $hasTotal = in_array('total', $columns);
        
        if (!$hasQuantity && !$hasTotal) {
            return null;
        }
        
        // Obtener la consulta base
        $baseQuery = $this->getRecordsOrderNotes($request, $model);
        
        // Crear una subconsulta para los totales
        $query = DB::connection('tenant')->table(DB::raw("({$baseQuery->toSql()}) as sub"))
            ->mergeBindings($baseQuery->getQuery());
        
        // Preparar la selección de columnas para el total
        $selectColumns = [];
        if ($hasQuantity) {
            $selectColumns[] = DB::raw('SUM(quantity) as total_quantity');
        }
        if ($hasTotal) {
            $selectColumns[] = DB::raw('SUM(total) as total_amount');
        }
        
        // Ejecutar la consulta con los totales
        $totals = $query->select($selectColumns)->first();
        
        // Preparar respuesta
        $result = [];
        if ($hasQuantity) {
            $result['total_quantity'] = $totals->total_quantity;
        }
        if ($hasTotal) {
            $result['total_amount'] = $totals->total_amount;
        }
        
        return $result;
    }
    public function excel(Request $request)
    {
        $query = $this->getRecordsOrderNotes($request->all(), OrderNoteItem::class);
        $data = $query->get();
        $columns = explode(',', $request->columns);
        $date_end = $request['date_end'] ?? now();
        $date_start = $request['date_start'] ?? now()->subDays(30);
        
        return Excel::download(
            new OrderNoteVariableExport($data, $columns, $date_end, $date_start), 
            'Reporte_Variable_Notas_Pedido_'.date('YmdHis').'.xlsx'
        );
    }
    public function pdf(Request $request)
    {
        $query = $this->getRecordsOrderNotes($request->all(), OrderNoteItem::class);
        
        // Seleccionar solo las columnas necesarias
        $columns = explode(',', $request->columns);
        
        // Definir columnas que necesitan agregación
        $aggregateColumns = [
            'item_quantity',
            'unit_price',
            'total'
        ];
        
        // Preparar columnas para select
        $selectColumns = [];
        $groupByColumns = [];
        
        foreach ($columns as $column) {
            switch ($column) {
                case 'person_type':
                    $selectColumns[] = 'person_types.description as person_type';
                    $groupByColumns[] = 'person_types.description';
                    break;
                case 'customer_number':
                    $selectColumns[] = 'persons.number as customer_number';
                    $groupByColumns[] = 'persons.number';
                    break;
                case 'customer_name':
                    $selectColumns[] = 'persons.name as customer_name';
                    $groupByColumns[] = 'persons.name';
                    break;
                case 'item_description':
                    $selectColumns[] = 'items.description as item_description';
                    $groupByColumns[] = 'items.description';
                    break;
                case 'delivery_date':
                    $selectColumns[] = 'order_notes.date_of_issue as delivery_date';
                    $groupByColumns[] = 'order_notes.date_of_issue';
                    break;
                case 'created_time':
                    $selectColumns[] = 'order_notes.created_at as created_time';
                    $groupByColumns[] = 'order_notes.created_at';
                    break;
                case 'item_quantity':
                    $selectColumns[] = DB::raw('SUM(order_note_items.quantity) as item_quantity');
                    break;
                case 'unit_price':
                    $selectColumns[] = DB::raw('MAX(order_note_items.unit_price) as unit_price');
                    break;
                case 'total':
                    $selectColumns[] = DB::raw('SUM(order_note_items.total) as total');
                    break;
            }
        }
        
        $query->select($selectColumns);
        
        if (!empty($groupByColumns)) {
            $query->groupBy($groupByColumns);
        }
        
        // Usar cursor en lugar de get para optimizar memoria
        $records = $query->cursor()->chunk(200);
        
        $columnLabels = [
            'person_type' => 'Tipo',
            'customer_number' => 'DNI',
            'customer_name' => 'Cliente',
            'item_description' => 'Producto',
            'delivery_date' => 'Fecha de entrega',
            'created_time' => 'Hora',
            'item_quantity' => 'Cantidad',
            'unit_price' => 'Precio',
            'total' => 'Monto'
        ];
        
        $columns = array_combine($columns, array_map(function($column) use ($columnLabels) {
            return $columnLabels[$column] ?? $column;
        }, $columns));

        $pdf = PDF::loadView('report::order_notes_variable.report_pdf', [
            'records' => $records,
            'columns' => $columns,
        ])->setPaper('a4', 'landscape')
          ->setOption('enable_font_subsetting', true)
          ->setOption('margin_left', 5)
          ->setOption('margin_right', 5)
          ->setOption('margin_top', 10)
          ->setOption('margin_bottom', 10)
          ->setOption('dpi', 72)
          ->setOption('isRemoteEnabled', false)
          ->setOption('isHtml5ParserEnabled', true)
          ->setOption('isFontSubsettingEnabled', true)
          ->setOption('tempDir', storage_path('app/public/temp'));

        $filename = 'Reporte_Variable_Notas_Pedido_'.date('YmdHis');
        
        gc_collect_cycles();
        
        return $pdf->stream($filename.'.pdf');
    }
}

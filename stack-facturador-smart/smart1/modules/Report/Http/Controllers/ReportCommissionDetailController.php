<?php

namespace Modules\Report\Http\Controllers;

use App\Models\Tenant\Catalogs\DocumentType;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Report\Exports\CommissionDetailExport;
use Illuminate\Http\Request;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\SaleNoteItem;
use App\Models\Tenant\User;
use App\Models\Tenant\DocumentItem;
use App\Models\Tenant\Company;
use App\Traits\CacheTrait;
use Carbon\Carbon;
use Modules\Report\Http\Resources\ReportCommissionDetailCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Hyn\Tenancy\Facades\TenancyFacade;

class ReportCommissionDetailController extends Controller
{
    use CacheTrait;
    public function filter()
    {
        $document_types = [];

        $establishments = Establishment::whereActive()
            ->select('id', 'description')
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->id,
                    'name' => $row->description
                ];
            })
            ->toArray();

        return compact('document_types', 'establishments');
    }

    public function index()
    {
        return view('report::commissions_detail.index');
    }

    public function records(Request $request)
    {
        $start = microtime(true);
        Log::info("Inicio de consulta de registros de comisiones: " . date('Y-m-d H:i:s'));

        $tenant_id = TenancyFacade::website()->uuid;
        $recordsKey = "tenant_{$tenant_id}_commission_records_" . md5(json_encode($request->all()));

        // Permitir que el cache dure más tiempo (1 hora) para mejorar rendimiento
        $time_query_start = microtime(true);

        // Uso de chunk para procesar lotes de registros y evitar problemas de memoria
        $sales_notes = $this->getRecords($request->all(), SaleNoteItem::class);
        Log::info("Tiempo obteniendo notas de venta: " . round(microtime(true) - $time_query_start, 2) . " segundos");

        $time_documents_start = microtime(true);
        $documents = $this->getRecords($request->all(), DocumentItem::class);
        Log::info("Tiempo obteniendo documentos: " . round(microtime(true) - $time_documents_start, 2) . " segundos");

        // Convertir los arrays a colecciones para poder usar union()
        $time_merge_start = microtime(true);
        $salesNotesCollection = collect($sales_notes);
        $documentsCollection = collect($documents);
        $rawRecords = $salesNotesCollection->merge($documentsCollection)->toArray();
        Log::info("Tiempo mezclando colecciones: " . round(microtime(true) - $time_merge_start, 2) . " segundos");
        Log::info("Total registros: " . count($rawRecords));

        // Usar la collection para transformar los datos de manera consistente
        $transform_start = microtime(true);
        $recordsCollection = new ReportCommissionDetailCollection(collect($rawRecords));
        $records = $recordsCollection->toArray($request);
        Log::info("Tiempo transformando datos para API: " . round(microtime(true) - $transform_start, 2) . " segundos");

        $end = microtime(true);
        Log::info("Tiempo total de consulta de comisiones: " . round($end - $start, 2) . " segundos");
        $items = $records;
        // Usar la paginación del lado del servidor para conjuntos grandes de datos
        // $page = $request->input('page', 1);
        // $perPage = $request->input('per_page', config('tenant.items_per_page', 20));
        // $offset = ($page - 1) * $perPage;

        // $total = count($records);
        // $items = array_slice($records, $offset, $perPage);

        return [
            'data' => $items,

        ];
    }

    public function getRecords($request, $model)
    {
        $start = microtime(true);
        Log::info("Iniciando getRecords para modelo: " . $model);

        $tenant_id = TenancyFacade::website()->uuid;
        $cacheKey = "tenant_{$tenant_id}_commission_get_records_" . md5(json_encode($request)) . "_" . $model;

        $result = Cache::remember($cacheKey, 3600, function () use ($request, $model, $start, $tenant_id) {
            $establishment_id = $request['establishment_id'];
            $period = $request['period'];
            $date_start = $request['date_start'];
            $date_end = $request['date_end'];
            $month_start = $request['month_start'];
            $month_end = $request['month_end'];
            $item_id = $request['item_id'];
            $unit_type_id = $request['unit_type_id'];

            $d_start = null;
            $d_end = null;

            switch ($period) {
                case 'month':
                    $d_start = Carbon::parse($month_start . '-01')->format('Y-m-d');
                    $d_end = Carbon::parse($month_start . '-01')->endOfMonth()->format('Y-m-d');
                    break;
                case 'between_months':
                    $d_start = Carbon::parse($month_start . '-01')->format('Y-m-d');
                    $d_end = Carbon::parse($month_end . '-01')->endOfMonth()->format('Y-m-d');
                    break;
                case 'date':
                    $d_start = $date_start;
                    $d_end = $date_start;
                    break;
                case 'between_dates':
                    $d_start = $date_start;
                    $d_end = $date_end;
                    break;
            }

            $query_start = microtime(true);
            $query = $this->data($establishment_id, $d_start, $d_end, $model, $item_id, $unit_type_id);
            Log::info("Tiempo construyendo query: " . round(microtime(true) - $query_start, 2) . " segundos");

            $execution_start = microtime(true);

            // Usar cursor para recorrer resultados grandes y reducir el uso de memoria
            $result = [];
            $count = 0;

            // Limitar campos a lo mínimo necesario y usar select para cargar solo lo necesario
            foreach ($query->cursor() as $item) {
                $count++;
                $result[] = $item->toArray();

                // Registrar progreso cada 500 registros
                if ($count % 500 === 0) {
                    Log::info("Procesados {$count} registros para {$model}");
                }
            }

            Log::info("Tiempo ejecutando query y procesando resultados: " . round(microtime(true) - $execution_start, 2) . " segundos");
            Log::info("Total de registros para {$model}: {$count}");

            return $result;
        });

        $end = microtime(true);
        Log::info("Tiempo total getRecords para {$model}: " . round($end - $start, 2) . " segundos");

        return $result;
    }

    private function data($establishment_id, $date_start, $date_end, $model, $item_id, $unit_type_id)
    {
        DB::enableQueryLog();
        $start = microtime(true);

        $baseFields = [
            'id',
            'item_id',
            'quantity',
            'unit_price',
            'item'
        ];

        if ($model == 'App\Models\Tenant\DocumentItem') {
            $baseFields[] = 'document_id';
        } else if ($model == 'App\Models\Tenant\SaleNoteItem') {
            $baseFields[] = 'sale_note_id';
        }

        $query = $model::query()
            ->select($baseFields);

        // Optimizar el proceso de carga para relaciones solo si son necesarias para los filtros
        // No cargar relaciones en la consulta principal, se cargarán luego en el ResourceCollection

        if ($model == 'App\Models\Tenant\DocumentItem') {
            if ($establishment_id) {
                $query->whereHas('document', function ($q) use ($date_start, $date_end, $establishment_id) {
                    $q->whereBetween('date_of_issue', [$date_start, $date_end])
                        ->whereIn('document_type_id', ['01', '03'])
                        ->whereNotIn('state_type_id', ['11', '09'])
                        ->where('establishment_id', $establishment_id);
                });
            } else {
                $query->whereHas('document', function ($q) use ($date_start, $date_end) {
                    $q->whereBetween('date_of_issue', [$date_start, $date_end])
                        ->whereIn('document_type_id', ['01', '03'])
                        ->whereNotIn('state_type_id', ['11', '09']);
                });
            }
        } else if ($model == 'App\Models\Tenant\SaleNoteItem') {
            if ($establishment_id) {
                $query->whereHas('sale_note', function ($q) use ($date_start, $date_end, $establishment_id) {
                    $q->whereBetween('date_of_issue', [$date_start, $date_end])
                        ->where('establishment_id', $establishment_id)
                        ->whereNotIn('state_type_id', ['11', '09'])
                        ->whereNotChanged();
                });
            } else {
                $query->whereHas('sale_note', function ($q) use ($date_start, $date_end) {
                    $q->whereBetween('date_of_issue', [$date_start, $date_end])
                        ->whereNotIn('state_type_id', ['11', '09'])
                        ->whereNotChanged();
                });
            }
        }

        if ($item_id) {
            $query->where('item_id', $item_id);
        }

        if ($item_id && $unit_type_id) {
            $query->where('item_id', $item_id)
                ->whereRaw("JSON_EXTRACT(item, '$.unit_type_id') = '" . $unit_type_id . "'");
        }

        // Limitar registros si son muchos para pruebas (quitar en producción)
        // $query->limit(500);

        $end = microtime(true);
        $queries = DB::getQueryLog();
        $lastQuery = end($queries);

        Log::info("Query construido para {$model}: " . ($lastQuery['query'] ?? 'N/A'));
        Log::info("Tiempo para construir query {$model}: " . round($end - $start, 2) . " segundos");

        return $query;
    }

    public function pdf(Request $request)
    {
        $start = microtime(true);
        Log::info("Iniciando generación de PDF: " . date('Y-m-d H:i:s'));

        $tenant_id = TenancyFacade::website()->uuid;
        $cacheKey = "tenant_{$tenant_id}_commission_pdf_" . md5(json_encode($request->all()));

        Log::info("Generando registros para PDF");
        $sales_notes = $this->getRecords($request->all(), SaleNoteItem::class);
        $documents = $this->getRecords($request->all(), DocumentItem::class);

        $salesNotesCollection = collect($sales_notes);
        $documentsCollection = collect($documents);
        $rawRecords = $salesNotesCollection->merge($documentsCollection)->toArray();

        // Usar la collection para transformar los datos
        $time_transform_start = microtime(true);
        $recordsCollection = new ReportCommissionDetailCollection(collect($rawRecords));
        $records = $recordsCollection->toArray($request);
        Log::info("Tiempo transformando datos para PDF: " . round(microtime(true) - $time_transform_start, 2) . " segundos");

        $company = Company::select('name', 'number')->first()->toArray();
        $establishment = ($request->establishment_id)
            ? Establishment::select('id', 'description')->findOrFail($request->establishment_id)->toArray()
            : auth()->user()->establishment->toArray();

        $data = [
            'records' => $records,
            'company' => $company,
            'establishment' => $establishment
        ];

        $pdf_start = microtime(true);
        $pdf = PDF::loadView('report::commissions_detail.report_pdf', $data)
            ->setPaper('a4', 'landscape');

        $end = microtime(true);
        Log::info("Tiempo generando PDF: " . round($end - $pdf_start, 2) . " segundos");
        Log::info("Tiempo total PDF: " . round($end - $start, 2) . " segundos");

        return $pdf->stream('Reporte_Utilidades_Detallado' . date('YmdHis') . '.pdf');
    }

    public function excel(Request $request)
    {
        $start = microtime(true);
        Log::info("Iniciando generación de Excel: " . date('Y-m-d H:i:s'));

        Log::info("Generando registros para Excel");
        $sales_notes = $this->getRecords($request->all(), SaleNoteItem::class);
        $documents = $this->getRecords($request->all(), DocumentItem::class);

        $salesNotesCollection = collect($sales_notes);
        $documentsCollection = collect($documents);
        $rawRecords = $salesNotesCollection->merge($documentsCollection)->toArray();

        // Usar la collection para transformar los datos
        $time_transform_start = microtime(true);
        $recordsCollection = new ReportCommissionDetailCollection(collect($rawRecords));
        $records = $recordsCollection->toArray($request);
        Log::info("Tiempo transformando datos para Excel: " . round(microtime(true) - $time_transform_start, 2) . " segundos");

        $company = Company::select('name', 'number')->first()->toArray();
        $establishment = ($request->establishment_id)
            ? Establishment::select('id', 'description')->findOrFail($request->establishment_id)->toArray()
            : auth()->user()->establishment->toArray();

        $data = [
            'records' => $records,
            'company' => $company,
            'establishment' => $establishment
        ];

        $excel_start = microtime(true);
        $result = (new CommissionDetailExport)
            ->records(collect($data['records']))
            ->company(collect($data['company']))
            ->establishment(collect($data['establishment']))
            ->download('Reporte_Comision_utilidades_Vendedor' . Carbon::now() . '.xlsx');

        $end = microtime(true);
        Log::info("Tiempo generando Excel: " . round($end - $excel_start, 2) . " segundos");
        Log::info("Tiempo total Excel: " . round($end - $start, 2) . " segundos");

        return $result;
    }
}

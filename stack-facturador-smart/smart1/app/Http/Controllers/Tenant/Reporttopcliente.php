<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Tenant\Person;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TopCustomersExport;

class Reporttopcliente extends Controller
{
    public function index(Request $request)
    {

        return view('clientestopreport.index');
    }

    private function getRecords(Request $request){

            // Filtro de fechas
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $include_nv = $request->input('include_nv') == 'true';
            // Consulta base para documentos
            $documentsQuery = DB::connection('tenant')->table('documents')
                ->select(
                    'persons.id',
                    'persons.name',
                    DB::raw('COUNT(documents.id) as purchases'),
                    DB::raw('SUM(documents.total) as total')
                )
                ->join('persons', 'documents.customer_id', '=', 'persons.id')
                ->whereIn('documents.state_type_id', ['01', '03', '05', '07', '13']) // Solo documentos válidos
                ->groupBy('persons.id', 'persons.name');
    
            // Aplicar el filtro de fechas si están presentes
            if ($startDate && $endDate) {
                $documentsQuery->whereBetween('documents.date_of_issue', [$startDate, $endDate]);
            }
    
            // Si se incluyen notas de venta, crear consulta para sale_notes
            if ($include_nv) {
                $saleNotesQuery = DB::connection('tenant')->table('sale_notes')
                    ->select(
                        'persons.id',
                        'persons.name',
                        DB::raw('COUNT(sale_notes.id) as purchases'),
                        DB::raw('SUM(sale_notes.total) as total')
                    )
                    ->join('persons', 'sale_notes.customer_id', '=', 'persons.id')
                    ->whereIn('sale_notes.state_type_id', ['01', '03', '05', '07', '13']) // Solo notas válidas
                    ->groupBy('persons.id', 'persons.name');
    
                // Aplicar el filtro de fechas a las notas de venta
                if ($startDate && $endDate) {
                    $saleNotesQuery->whereBetween('sale_notes.date_of_issue', [$startDate, $endDate]);
                }
    
                // Combinar ambas consultas usando UNION
                $combinedQuery = $documentsQuery->union($saleNotesQuery);
    
                // Agrupar y sumar los resultados combinados
                $topCustomers = DB::connection('tenant')
                    ->table(DB::raw("({$combinedQuery->toSql()}) as combined_sales"))
                    ->mergeBindings($combinedQuery)
                    ->select(
                        'id',
                        'name',
                        DB::raw('SUM(purchases) as purchases'),
                        DB::raw('SUM(total) as total')
                    )
                    ->groupBy('id', 'name')
                    ->orderBy('purchases', 'desc')
                    ->limit(10)
                    ->get();
    
            } else {
                // Solo documentos
                $topCustomers = $documentsQuery->orderBy('purchases', 'desc')->get();
            }
            return $topCustomers;
    }
    public function records(Request $request)
    {
        $records = $this->getRecords($request);
        return response()->json($records);
    }

    public function export(Request $request)
    {
        // Obtener parámetros de la URL
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $includeNv = $request->get('include_nv') == 'true'; // Convertir a boolean
        
        // Debug - puedes remover esto después
    
        
        return Excel::download(
            new TopCustomersExport($startDate, $endDate, $includeNv), 
            'top_customers.xlsx'
        );
    }
}

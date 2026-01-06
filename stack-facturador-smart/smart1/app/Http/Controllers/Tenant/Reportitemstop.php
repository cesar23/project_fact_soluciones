<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TopItemsExport;

class Reportitemstop extends Controller
{
    public function index(Request $request)
    {
        // Verificar que las tablas existen
        if (!DB::connection('tenant')->getSchemaBuilder()->hasTable('documents') || 
            !DB::connection('tenant')->getSchemaBuilder()->hasTable('document_items') || 
            !DB::connection('tenant')->getSchemaBuilder()->hasTable('items')) {
            return view('itemstopreport.index')->withErrors('Las tablas necesarias no existen en la base de datos.');
        }

        // Filtro de fechas
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Consulta para obtener los items y el número de ventas realizadas y la suma total
        $query = DB::connection('tenant')->table('document_items')
            ->select('items.id', 'items.description', DB::raw('COUNT(document_items.id) as sales_count'), DB::raw('SUM(document_items.total) as total_sales'))
            ->join('items', 'document_items.item_id', '=', 'items.id')
            ->join('documents', 'document_items.document_id', '=', 'documents.id')
            ->groupBy('items.id', 'items.description')
            ->orderBy('sales_count', 'desc');

        // Aplicar el filtro de fechas si están presentes
        if ($startDate && $endDate) {
            $query->whereBetween('documents.date_of_issue', [$startDate, $endDate]);
        }

        $topItems = $query->get();

        return view('itemstopreport.index', compact('topItems', 'startDate', 'endDate'));
    }

    public function export(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
    
        return Excel::download(new TopItemsExport($startDate, $endDate), 'top_items.xlsx');
    }
    
}

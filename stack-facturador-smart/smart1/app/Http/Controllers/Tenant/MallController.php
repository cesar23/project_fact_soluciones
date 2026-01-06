<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\MallDocumentCollection;
use App\Models\Tenant\Company;
use App\Models\Tenant\Document;
use App\Models\Tenant\MallConfig;
use App\Models\Tenant\User;
use Illuminate\Http\Request;

class MallController extends Controller
{
    public function index()
    {
        $company = Company::first();
        return view(
            'tenant.mall.index',
            compact('company')
        );
    }

    public function set_config(Request $request)
    {
        $mall_config = MallConfig::first();
        if (!$mall_config) {
            $mall_config = new MallConfig();
        }
        $mall_config->store_id = $request->store_id;
        $mall_config->store_name = $request->store_name;
        $mall_config->mall_id = $request->mall_id;
        $mall_config->store_number = $request->store_number;
        $mall_config->save();

        return response()->json([
            'success' => true,
            'message' => 'Configuración guardada correctamente'
        ]);
    }
    public function export_csv_sales(Request $request)
    {
        $mall_config = MallConfig::first();
        if (!$mall_config) {
            return response()->json([
                'success' => false,
                'message' => 'No se ha configurado la tienda'
            ]);
        }
        $filename = 'VENTAS_' . $mall_config->mall_id . '_' . $mall_config->store_number . '_' . date('YmdHis') . '.csv';
        $headers = ["sale_id", "store_id", "seller_id", "sale_date", "qty", "total_amount", "mall_id", "store_number"];
        // $handle = fopen($filename, 'w+');
        $path = storage_path('app/public/' . $filename);
        $handle = fopen($path, 'w+');
        fputcsv($handle, $headers, ';');
        $records  = $this->getRecords($request)->get();

        foreach ($records as $row) {
            $date_of_issue = $row->date_of_issue->format('Y-m-d');
            $time_of_issue = $row->time_of_issue;
            $date_and_time =  $date_of_issue . ' ' . $time_of_issue;
            $total =  $row->document_type_id === '07' ? $row->total_value * -1 : $row->total_value;
            $data = [
                $row->id,
                $mall_config->store_id,
                $row->seller_id,
                $date_and_time,
                $row->items->sum('quantity'),
                $total,
                $mall_config->mall_id,
                $mall_config->store_number,
            ];
            $line = implode(';', $data) . "\n";
            fwrite($handle, $line);
        }
        fclose($handle);
        $headers = array(
            'Content-Type' => 'text/csv',
        );
        return response()->download($path, $filename, $headers);
    }
    public function export_csv_sellers_by_id(Request $request)
    {
        $ids = explode(',', $request->ids);
        $mall_config = MallConfig::first();
        if (!$mall_config) {
            return response()->json([
                'success' => false,
                'message' => 'No se ha configurado la tienda'
            ]);
        }
        $filename = 'VENDEDORES_' . $mall_config->mall_id . '_' . $mall_config->store_number . '_' . date('YmdHis') . '.csv';
        $headers = ["seller_id", "seller_name", "mall_id", "store_number"];
        $path = storage_path('app/public/' . $filename);
        $handle = fopen($path, 'w+');
        $headerLine = implode(';', $headers) . "\n";
        fwrite($handle, $headerLine);
        $records  = User::whereIn('id', $ids)->get()->map(function ($row) {
            return [
                'seller_id' => $row->id,
                'seller_name' => $row->name,
            ];
        });
    
        foreach ($records as $row) {
            $data = [
                $row['seller_id'],
                $row['seller_name'],
                $mall_config->mall_id,
                $mall_config->store_number,
            ];
            $dataLine = implode(';', $data) . "\n";
            fwrite($handle, $dataLine);
        }
    
        fclose($handle);
        $headers = array(
            'Content-Type' => 'text/csv',
        );
        return response()->download($path, $filename, $headers);
    }
    public function export_csv_sellers(Request $request)
    {
        $mall_config = MallConfig::first();
        if (!$mall_config) {
            return response()->json([
                'success' => false,
                'message' => 'No se ha configurado la tienda'
            ]);
        }
        $filename = 'VENDEDORES_' . $mall_config->mall_id . '_' . $mall_config->store_number . '_' . date('YmdHis') . '.csv';
        
        $headers = ["seller_id", "seller_name", "mall_id", "store_number"];
        $path = storage_path('app/public/' . $filename);
        $handle = fopen($path, 'w+');
        $headerLine = implode(';', $headers) . "\n";
        fwrite($handle, $headerLine);
        $records  = $this->getRecords($request)->get()->groupBy('seller_id')->map(function ($row) {
            return [
                'seller_id' => $row->first()->seller->id,
                'seller_name' => $row->first()->seller->name,
            ];
        });
    
        foreach ($records as $row) {
            $data = [
                $row['seller_id'],
                $row['seller_name'],
                $mall_config->mall_id,
                $mall_config->store_number,
            ];
            $dataLine = implode(';', $data) . "\n";
            fwrite($handle, $dataLine);
        }
    
        fclose($handle);
        $headers = array(
            'Content-Type' => 'text/csv',
        );
        return response()->download($path, $filename, $headers);
    }
    public function users()
    {
        $users = User::where('type', '<>', 'superadmin')
            ->where('type', '<>', 'client')
            ->get();
        return response()->json([
            'success' => true,
            'data' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'selected' => false,
                ];
            })
        ]);
    }
    public function export_csv_company()
    {
        $mall_config = MallConfig::first();
        $data = [
            'store_id' => $mall_config->store_id,
            'store_name' => $mall_config->store_name,
            'mall_id' => $mall_config->mall_id,
            'store_number' => $mall_config->store_number,
        ];
        $filename = 'TIENDAS_' . $mall_config->mall_id . '_' . $mall_config->store_number . '_' . date('YmdHis') . '.csv';
        $path = storage_path('app/public/' . $filename);
        $handle = fopen($path, 'w+');
        $headerLine = implode(';', array_keys($data)) . "\n";
        $dataLine = implode(';', array_values($data)) . "\n";
        fwrite($handle, $headerLine);
        fwrite($handle, $dataLine);
        fclose($handle);
        $headers = array(
            'Content-Type' => 'text/csv',
        );
        return response()->download($path, $filename, $headers);
    }
    public function get_config()
    {
        $mall_config = MallConfig::first();

        return response()->json([
            'success' => true,
            'data' => $mall_config
        ]);
    }
    public function columns()
    {
        return [
            'date_of_issue' => 'Fecha de emisión',
            'between_dates' => 'Entre fechas',
        ];
    }
    public function filter()
    {
        return [
            'success' => true,
        ];
    }
    public function records(Request $request)
    {
        $records = $this->getRecords($request);

        return new MallDocumentCollection($records->paginate(config('tenant.items_per_page')));
    }

    private function getRecords($request)
    {
        $company = Company::first();
        $date_of_issue = $request->value;
        $column = $request->column;
        $end_dates = $request->end_dates;
        $soap_type_id = $company->soap_type_id;
        $documents = Document::where('date_of_issue', $date_of_issue);
        if ($end_dates && $column === 'between_dates') {
            $documents = Document::whereBetween('date_of_issue', [$date_of_issue, $end_dates]);
        }
        if ($soap_type_id !== '01') {
            $documents = $documents->where('state_type_id', '05');
        }
        return $documents;
    }
}

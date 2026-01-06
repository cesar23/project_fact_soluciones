<?php

namespace Modules\Purchase\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use App\Models\Tenant\Warehouse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Purchase\Imports\PurchaseSeriesImport;
use Maatwebsite\Excel\Excel;

class PurchaseController extends Controller
{
    public function recordTransfer($id)
    {
        $record = DB::connection('tenant')
            ->table('purchases')
            ->where('id', $id)
            ->first();
            if(!$record) {
                return [
                    'success' => false,
                    'message' => 'No se encontró el registro'
                ];
            }
        $establishment_id = $record->establishment_id;
        $warehouse_id = DB::connection('tenant')->table('warehouses')->where('establishment_id', $establishment_id)->first()->id;
        $supplier = json_decode($record->supplier);
        $data = [
            'supplier' => $supplier->number . " - " . $supplier->name,
            'purchase' => $record->series . "-" . $record->number,
            'date_of_issue' => $record->date_of_issue,
            'warehouse_id' => $warehouse_id,
            'warehouse_destination_id' => null,
            'description' => null,
        ];
        return [
            'success' => true,
            'message' => 'Registro encontrado',
            'data' => $data
        ];
    }

    public function tablesTransfer()
    {
        return [
            'warehouses' => Warehouse::all()->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'description' => $row->description,
                ];
            }),
            'users' => User::whereActive()->get()->transform(function ($row) {
                return [
                    'id' => $row->id,
                    'name' => $row->name,
                ];
            }),
        ];
    }
    public function importSeries(Request $request)
    {

        if ($request->hasFile('file')) {

            try {

                $import = new PurchaseSeriesImport();
                $import->import($request->file('file'), null, Excel::XLSX);
                $data = $import->getData();

                return [
                    'success' => true,
                    'message' =>  __('app.actions.upload.success'),
                    'data' => $data
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' =>  $e->getMessage()
                ];
            }
        }
        return [
            'success' => false,
            'message' =>  __('app.actions.upload.error'),
        ];
    }
}

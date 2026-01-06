<?php

namespace Modules\Account\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Account\Http\Resources\AccountPeriodCollection;
use Modules\Account\Http\Resources\AccountPeriodResource;
use Modules\Account\Models\AccountPeriod;

class AccountPeriodController extends Controller
{
    public function index()
    {
        return view('account::periods.index');
    }

    public function records()
    {
        $records = AccountPeriod::orderBy('year', 'desc')->get();
        
        // Formatear los registros para la interfaz
        $formatted_records = $records->map(function($record) {
            return [
                'id' => $record->id,
                'year' => $record->year->format('Y'),  // Año formateado
                'total_debit' => $record->total_debit,
                'total_credit' => $record->total_credit,
                'balance' => $record->balance
            ];
        });
        
        return [
            'success' => true,
            'data' => $formatted_records
        ];
    }

    public function record($id)
    {
        $record = AccountPeriod::findOrFail($id);
        return new AccountPeriodResource($record);
    }

    public function store(Request $request)
    {
        try {
            DB::connection('tenant')->beginTransaction();
            
            $id = $request->input('id');
            $period = AccountPeriod::findOrNew($id);
            $period->fill($request->all());
            $period->save();
            
            DB::connection('tenant')->commit();
            
            return [
                'success' => true,
                'message' => $id ? 'Período actualizado' : 'Período creado',
                'id' => $period->id
            ];
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error($e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function destroy($id)
    {
        try {
            $period = AccountPeriod::findOrFail($id);
            $period->delete();
            
            return [
                'success' => true,
                'message' => 'Período eliminado'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
} 
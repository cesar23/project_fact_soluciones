<?php

namespace Modules\Account\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Account\Models\LedgerAccount;
use App\Models\System\LedgerAccountDescription;
use App\Models\System\LedgerAccountMovement;
use App\Models\System\LedgerAccountRecognition;
use App\Models\System\SubDiary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Account\Http\Resources\AccountSubDiaryResource;
use Modules\Account\Http\Resources\System\LedgerAccountCollection;
use Modules\Account\Http\Resources\System\SubdiaryCollection;
use Modules\Account\Models\AccountMonth;
use Modules\Account\Models\AccountSubDiary;

class AccountSubDiaryController extends Controller
{
    public function create($month_id)
    {
        $system_sub_diaries = new SubdiaryCollection(SubDiary::all());
        $month = \Modules\Account\Models\AccountMonth::findOrFail($month_id);
        $period = $month->accountPeriod;

        return view('account::sub_diaries.create', compact('system_sub_diaries', 'month', 'period'));
    }

    public function createWithSelection()
    {
        $system_sub_diaries = new SubdiaryCollection(SubDiary::all());
        return view('account::sub_diaries.create_with_selection', compact('system_sub_diaries'));
    }

    public function index($month_id = null)
    {
        if ($month_id) {
            $month = \Modules\Account\Models\AccountMonth::findOrFail($month_id);
            return view('account::sub_diaries.index', compact('month'));
        }
        return view('account::sub_diaries.index');
    }

    public function record()
    {
        $sub_diary = AccountSubDiary::first();
        $record = new AccountSubDiaryResource($sub_diary);

        return $record;
    }

    public function records($month_id = null)
    {
        $query = AccountSubDiary::with(['items', 'accountMonth']);
        $month = AccountMonth::findOrFail($month_id);
        $month_number = $month->month->format('m');
        if ($month_id) {
            $query->where('account_month_id', $month_id);
        }
        
        // Ordenar por código, luego por correlative_number
        $records = $query
                        ->orderBy('code', 'asc')
                        ->orderByRaw('CAST(correlative_number AS UNSIGNED)')
                        ->get();
        
        $formatted_records = $records->map(function($record) use ($month_number) {
            $total_debit = $record->items->sum('debit_amount');
            $total_credit = $record->items->sum('credit_amount');
            $prefix = "$record->code-$month_number".str_pad($record->correlative_number, 4, '0', STR_PAD_LEFT);
            return [
                'id' => $record->id,
                'code' => $record->code,
                'prefix' => $prefix,
                'date' => $record->date->format('Y-m-d'),
                'description' => $record->description,
                'book_code' => $record->book_code,
                'complete' => $record->complete,
                'is_manual' => $record->is_manual,
                'total_debit' => $total_debit,
                'total_credit' => $total_credit,
                'items_count' => $record->items->count(),
                'items' => $record->items->map(function($item) {
                    return [
                        'id' => $item->id,
                        'code' => $item->code,
                        'description' => $item->description,
                        'general_description' => $item->general_description,
                        'document_number' => $item->document_number,
                        'correlative_number' => $item->correlative_number,
                        'debit' => $item->debit,
                        'credit' => $item->credit,
                        'debit_amount' => $item->debit_amount,
                        'credit_amount' => $item->credit_amount,
                        'amount_adjustment' => $item->amount_adjustment
                    ];
                })
            ];
        });
        
        return [
            'success' => true,
            'data' => $formatted_records
        ];
    }

    public function store(Request $request)
    {
        try {
            DB::connection('tenant')->beginTransaction();
            $id = $request->input('id');
            $sub_diary = AccountSubDiary::findOrNew($id);
            $correlative_number_general = 1;
            if(!$request->input('code')){
                $request->merge(['code' => "05"]);
            }
            if(!$id){
                $last_sub_diary = AccountSubDiary::where('account_month_id', $request->input('account_month_id'))
                ->where('code', $request->input('code'))
                ->orderBy('id', 'desc')->first();
                if($last_sub_diary){
                    $correlative_number_general = $last_sub_diary->correlative_number + 1;
                    $request->merge(['correlative_number' => $correlative_number_general]);
                }else{
                    $request->merge(['correlative_number' => $correlative_number_general]);
                }
            }
        



            $general_description = $request->input('general_description');
            if(!$general_description){
                $request->merge(['general_description' => $request->input('description')]);
            }
            $sub_diary->fill($request->all());
            $sub_diary->save();
            $items = $request->input('items');
            $sub_diary->items()->delete();
            $correlative_number = 1;
            $debit_amount = 0;
            $credit_amount = 0;
            foreach ($items as $item) {
                $item['correlative_number'] = $correlative_number;
                $item['account_sub_diary_id'] = $sub_diary->id;
                $sub_diary->items()->create($item);
                $correlative_number++;
                $debit_amount += $item['debit_amount'];
                $credit_amount += $item['credit_amount'];
            }

            // Actualizar los totales en el mes contable
            if ($request->has('account_month_id')) {
                $month = \Modules\Account\Models\AccountMonth::findOrFail($request->input('account_month_id'));
                $month->calculateBalance();

                // Actualizar también el período
                $period = $month->accountPeriod;
                $period->calculateBalance();
            }

            DB::connection('tenant')->commit();
            return [
                'success' => true,
                'message' => 'Subdiario guardado correctamente'
            ];
        } catch (\Exception $e) {
            Log::error($e->getTraceAsString());
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function detail($detail)
    {
        $code_description = substr($detail, 0, 3);
        $code_movement_recognition = substr($detail, 0, 2);

        $account_description = LedgerAccountDescription::where('code', $code_description)->first();
        $account_recognition = LedgerAccountRecognition::where('code', $code_movement_recognition)->first();
        $account_movement = LedgerAccountMovement::where('code', $code_movement_recognition)->first();

        return response()->json([
            'description' => $account_description->description,
            'content' => $account_recognition->content,
            'recognition' => $account_recognition->recognition,
            'comments' => $account_recognition->comments,
            'debit_description' => $account_movement->debit_description,
            'credit_description' => $account_movement->credit_description,
        ]);
    }

    public function destroy($id)
    {
        try {
            $sub_diary = AccountSubDiary::findOrFail($id);
            $month_id = $sub_diary->account_month_id;
            
            DB::connection('tenant')->beginTransaction();
        
            
            // Eliminamos los items primero
            $sub_diary->items()->delete();
            $sub_diary->delete();
            
            // Actualizamos totales del mes
            if ($month_id) {
                $month = \Modules\Account\Models\AccountMonth::findOrFail($month_id);
                $items = $month->items;
                $total_debit = 0;
                $total_credit = 0;
                foreach ($items as $item) {
                    $total_debit += $item->debit_amount;
                    $total_credit += $item->credit_amount;
                }
                $balance = $total_debit - $total_credit;
                
                $month->total_debit = $total_debit;
                $month->total_credit = $total_credit;
                $month->balance = $balance;
                $month->save();

                // Actualizar también el período
                $period = $month->accountPeriod;
                $period_total_debit = $period->months()->sum('total_debit');
                $period_total_credit = $period->months()->sum('total_credit');
                $period_balance = $period_total_debit - $period_total_credit;
                
                $period->total_debit = $period_total_debit;
                $period->total_credit = $period_total_credit;
                $period->balance = $period_balance;
                $period->save();
            }
            
            DB::connection('tenant')->commit();
            
            return [
                'success' => true,
                'message' => 'Subdiario eliminado correctamente'
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

    /**
     * Obtener los items de un subdiario específico
     */
    public function items($id)
    {
        try {
            $sub_diary = AccountSubDiary::findOrFail($id);
            $items = $sub_diary->items()->get();
            
            $formatted_items = $items->map(function($item) {
                return [
                    'id' => $item->id,
                    'code' => $item->code,
                    'description' => $item->description,
                    'general_description' => $item->general_description,
                    'document_number' => $item->document_number,
                    'correlative_number' => $item->correlative_number,
                    'debit' => $item->debit,
                    'credit' => $item->credit,
                    'debit_amount' => $item->debit_amount,
                    'credit_amount' => $item->credit_amount
                ];
            });
            
            return [
                'success' => true,
                'data' => $formatted_items
            ];
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getAccountByCode($code){
        $account = LedgerAccount::where('code', $code)->first();
        if($account){
            return response()->json([
                'success' => true,
                'data' => $account
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'No se encontró la cuenta'
        ]);
    }
    public function getAccounts(Request $request){
        $records = LedgerAccount::query();
        $records->when($request->code, function ($query, $value) {
            return $query->where('code', 'like', '%' . $value . '%');
        });
        $records->when($request->name, function ($query, $value) {
            return $query->where('name', 'like', '%' . $value . '%');
        });
        $records->where(DB::raw('LENGTH(code)'), '>=', 5);

        return new LedgerAccountCollection($records->paginate(20));
    }
    public function getAccountsByNameOrCode(Request $request){
        $records = LedgerAccount::where(DB::raw('LENGTH(code)'), '=', 6);
        $value = $request->input('value');
        if($value){
            $records->where(function($query) use ($value){
                $query->where('code', 'like', $value . '%')
                    ->orWhere('name', 'like', '%' . $value . '%');
            });
        }

        return new LedgerAccountCollection($records->paginate(20));
    }


}

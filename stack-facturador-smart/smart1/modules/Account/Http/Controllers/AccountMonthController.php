<?php

namespace Modules\Account\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Account\Exports\MajorExport;
use Modules\Account\Exports\MonthBalanceExport;
use Modules\Account\Exports\MonthDiaryExport;
use Modules\Account\Exports\MonthDiarySimplifiedExport;
use Modules\Account\Http\Resources\AccountMonthResource;
use Modules\Account\Models\AccountMonth;
use Modules\Account\Models\AccountPeriod;
use Modules\Account\Models\AccountSubDiary;
use Modules\Account\Models\AccountSubDiaryItem;

class AccountMonthController extends Controller
{
    public function index($period_id)
    {
        $period = AccountPeriod::findOrFail($period_id);
        return view('account::months.index', compact('period'));
    }
    private function translateMonth($month)
    {
        $translations = [
            'January' => 'Enero',
            'February' => 'Febrero',
            'March' => 'Marzo',
            'April' => 'Abril',
            'May' => 'Mayo',
            'June' => 'Junio',
            'July' => 'Julio',
            'August' => 'Agosto',
            'September' => 'Septiembre',
            'October' => 'Octubre',
            'November' => 'Noviembre',
            'December' => 'Diciembre',
        ];

        return $translations[$month] ?? $month;
    }
    public function records($period_id)
    {
        $records = AccountMonth::where('account_period_id', $period_id)
            ->orderBy('month', 'asc')
            ->get();

        // Agregar el nombre del mes formateado para mostrar en la interfaz
        $formatted_records = $records->map(function ($record) {
            $month_date = $record->month;
            return [
                'id' => $record->id,
                'month' => $month_date,
                'month_name' => $this->translateMonth($month_date->format('F')),  // Nombre completo del mes y año
                'total_debit' => number_format($record->total_debit, 2, '.', ','),
                'total_credit' => number_format($record->total_credit, 2, '.', ','),
                'balance' => number_format($record->balance, 2, '.', ','),
                'account_period_id' => $record->account_period_id,
                'last_syncronitation' => $record->last_syncronitation
            ];
        });
        return [
            'success' => true,
            'data' => $formatted_records
        ];
    }

    public function record($id)
    {
        $record = AccountMonth::findOrFail($id);
        return new AccountMonthResource($record);
    }

    public function store(Request $request)
    {
        try {
            DB::connection('tenant')->beginTransaction();
            // Validar que el mes y año sean únicos
            $month_date = Carbon::parse($request->input('month'));
            $exists = AccountMonth::where('account_period_id', $request->input('account_period_id'))
                ->whereYear('month', $month_date->year)
                ->whereMonth('month', $month_date->month)
                ->where('id', '!=', $request->input('id'))
                ->exists();

            if ($exists) {
                throw new \Exception('Ya existe un registro para el mes y año seleccionado');
            }
            $id = $request->input('id');
            $month = AccountMonth::findOrNew($id);
            $month->account_period_id = $request->input('account_period_id');
            $period = AccountPeriod::findOrFail($request->input('account_period_id'));
            $year = $period->year;
            $month_request = $request->input('month');
            // Obtener la fecha del mes solicitado y cambiar el año
            $month_date = Carbon::parse($month_request);
            $year_date = Carbon::parse($year);
            $month_request = $month_date->setYear($year_date->year)->toISOString();
            $request->merge(['month' => $month_request]);
            $month->fill($request->all());
            $month->save();

            DB::connection('tenant')->commit();

            return [
                'success' => true,
                'message' => $id ? 'Mes actualizado' : 'Mes creado',
                'id' => $month->id
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
            $month = AccountMonth::findOrFail($id);
            $month->delete();

            return [
                'success' => true,
                'message' => 'Mes eliminado'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    private function getAccountsBalance($length = 3)
    {
        if($length == 5){
            $accounts = DB::connection('tenant')->table('ledger_accounts_tenant')->where(function($query) use ($length){
                $query->where(DB::raw('LENGTH(code)'), $length)
                ->orWhere(DB::raw('LENGTH(code)'), $length + 1);
            })->get();

            $codes_with_length_5 = $accounts->filter(function($account) {
                return strlen($account->code) == 5;
            })->pluck('code');

            $accounts = $accounts->filter(function($account) use ($codes_with_length_5) {

                if (strlen($account->code) == 6) {
                    $prefix = substr($account->code, 0, 5);
                    if ($codes_with_length_5->contains($prefix)) {
                        return false;
                    }
                }
                return true;
            })->values();

        }else{
            $accounts = DB::connection('tenant')->table('ledger_accounts_tenant')->where(DB::raw('LENGTH(code)'), $length)->get();
        
        
        }
        return $accounts;
    }
    public function exportBalanceAnnual(Request $request, $id)
    {
        $length = $request->input('digits');
        $period_id = $id;
        $accounts = $this->getAccountsBalance($length);

        $company = DB::connection('tenant')->table('companies')->select('name')->first();
        $records = AccountSubDiaryItem::whereHas('subDiary', function ($query) use ($period_id) {
            $query->whereHas('accountMonth', function ($query) use ($period_id) {
                $query->where('account_period_id', $period_id);
            });
        })
            ->select('code', 'description')
            ->selectRaw('SUM(credit_amount) as total_credit')
            ->selectRaw('SUM(debit_amount) as total_debit')
            ->selectRaw('SUM(credit_amount) - SUM(debit_amount) as balance')
            ->groupBy('code', 'description')
            ->get();

        $period = AccountPeriod::findOrFail($period_id);
        $name_file = 'balance_anual_'.$period->year.'.xlsx';
        return Excel::download(new MonthBalanceExport($records, $company, $accounts,$length), $name_file);
    }
    public function exportBalanceMonthly(Request $request, $id)
    {
        $length = $request->input('digits');
        $accounts = $this->getAccountsBalance($length);

        $company = DB::connection('tenant')->table('companies')->select('name')->first();
        $records = AccountSubDiaryItem::whereHas('subDiary', function ($query) use ($id) {
            $query->where('account_month_id', $id);
        })
            ->select('code', 'description')
            ->selectRaw('SUM(credit_amount) as total_credit')
            ->selectRaw('SUM(debit_amount) as total_debit')
            ->selectRaw('SUM(debit_amount) - SUM(credit_amount) as balance')
            ->groupBy('code', 'description')
            ->get();
        $account_month = AccountMonth::findOrFail($id);
        $name_file = 'balance_mensual_'.$account_month->month->format('Y-m').'.xlsx';
        return Excel::download(new MonthBalanceExport($records, $company, $accounts,$length), $name_file);
    }

    public function exportDiary($id)
    {
        $records = AccountSubDiary::where('account_month_id', $id)->
        orderBy('code', 'asc')->
        orderByRaw('CAST(correlative_number AS UNSIGNED)')->
        get();
        $month = AccountMonth::findOrFail($id);
        $period = $month->month->format('Y-m');
        $company = DB::connection('tenant')->table('companies')->select('name','number')->first();
        $name_file = 'diario_mensual_'.$month->month->format('Y-m').'.xlsx';
        return Excel::download(new MonthDiaryExport($records, $company, $month, $period), $name_file);
    }
    public function exportDiarySimplified($id)
    {
        $records = AccountSubDiary::where('account_month_id', $id)->get();
        $month = AccountMonth::findOrFail($id);
        $period = $month->month->format('Y-m');
        $company = DB::connection('tenant')->table('companies')->select('name','number')->first();
        $name_file = 'diario_simplificado_'.$month->month->format('Y-m').'.xlsx';
        return Excel::download(new MonthDiarySimplifiedExport($records, $company, $month, $period), $name_file);
    }
    private function getNameByCode($code) {
        $description = DB::connection('tenant')->table('ledger_accounts_tenant')->where('code', $code)->select('name')->first();
        return $description->name;
    }

    public function exportMajor(Request $request, $period_id)
    {
        $accounts = [];
        $digits = $request->input('digits');
        $names = collect([]);
        $period_db = AccountPeriod::findOrFail($period_id);
        $company = DB::connection('tenant')->table('companies')->select('name','number')->first();
        AccountSubDiary::with('items')->whereHas('accountMonth', function ($query) use ($period_id) {
            $query->where('account_period_id', $period_id);
        })
            ->chunk(500, function ($records) use (&$accounts, $digits, &$names) {
                foreach ($records as $idx => $record) {
                    $month = $record->date->format('m');
                    $prefix = '05-' . $month;
                    $correlative_number = $prefix . '' . str_pad($idx +1, 4, '0', STR_PAD_LEFT);
                    foreach ($record->items as $item) {
                        $code = substr($item->code, 0, $digits);
                        if (isset($accounts[$code])) {
                            $accounts[$code]['items'][] = [
                                'date' => $record->date->format('d/m/Y'),
                                'date_without_format' => $record->date,
                                'correlative_number' => $correlative_number,
                                'debit_amount' => $item->debit_amount,
                                'credit_amount' => $item->credit_amount,
                                'general_description' => $item->general_description,
                            ];
                        } else {
                            $name = $names->get($code);
                            if(!$name){
                                $name = $this->getNameByCode($code);
                                $names->put($code, $name);
                            }
                            $accounts[$code] = [
                                'code' => $code,
                                'description' => $name,
                                'items' => [
                                        [
                                            'date' => $record->date->format('d/m/Y'),
                                            'date_without_format' => $record->date,
                                            'correlative_number' => $correlative_number,
                                            'debit_amount' => $item->debit_amount,
                                            'credit_amount' => $item->credit_amount,
                                            'general_description' => $item->general_description,
                                        ]
                                ]
                            ];
                        }
                    }
                }
            });

            $this->orderAccounts($accounts);
            $period = $period_db->year->format('Y');
            $name_file = 'mayor_'.$period.'.xlsx';
            return Excel::download(new MajorExport($accounts, $company, $period), $name_file);
    }

    private function orderAccounts(&$accounts)
    {
        // Ordenar cuentas por su código (clave del array)
        $accounts = collect($accounts)
            ->sortBy(function ($acc, $code) {
                return $code;
            })
            ->toArray();

        // Reescribir cada bloque de items ordenado por fecha
        foreach ($accounts as $code => $account) {
            $accounts[$code]['items'] = collect($account['items'])
                ->sortBy(function ($item) {
                    return $item['date_without_format'];
                })
                ->sortBy(function ($item) {
                    return $item['correlative_number'];
                })
                ->values()
                ->toArray();
        }
    }
}

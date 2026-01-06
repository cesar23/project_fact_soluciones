<?php

namespace Modules\Account\Http\Controllers;

use App\CoreFacturalo\Helpers\Functions\FunctionsHelper;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Company;
use App\Models\Tenant\Document;
use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\Retention;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Account\Exports\LedgerAccountExcelExport;
use Modules\Account\Models\AccountingLedger;
use Modules\Account\Models\LedgerAccount;
use Modules\Account\Http\Resources\LedgerAccountCollection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\DB;
use Modules\Account\Exports\LedgerAccountTenantExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Account\Models\AccountPeriod;

class LedgerAccountController extends Controller
{

    public function form0710($period_id)
    {
        $company = Company::active();
        $state_result = collect([]);
        $accounts = [
            '10', '11', '12', '13', '14', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '28', '29', '27', '30', '31', '32', '33', 
             '34', '35', '36', '37', '38', '50', '51', '56', '57', '58',
              '591101', '592101', '591102', '592102',
            '40', '41', '42', '43', '44', '45', '46', '47', '48', '49',
            '66', '74', '69', '94', '95', '97', '77', '87', '88'

        ];
        $period_db = AccountPeriod::find($period_id);
        $period = $period_db->year->format('Y');

        $sum_accounts = DB::connection('tenant')
            ->table('account_sub_diary_items')
            ->join('account_sub_diaries', 'account_sub_diary_items.account_sub_diary_id', '=', 'account_sub_diaries.id')
            ->join('account_months', 'account_months.id', '=', 'account_sub_diaries.account_month_id')
            ->join('account_periods', 'account_periods.id', '=', 'account_months.account_period_id')
            ->where('account_periods.id', $period_id)
            ->where(function ($query) use ($accounts) {
                // Cuentas base
                foreach ($accounts as $account) {
                    $query->orWhere('account_sub_diary_items.code', 'like', $account . '%');
                }

                // Grupos especiales con sub-excepciones
                $special_groups = [
                    '39' => ['398'],
                    '52' => ['529'],
                    '65' => ['655'],
                    '70' => ['709'],
                    '75' => ['756', '759'],
                ];

                foreach ($special_groups as $group => $exceptions) {
                    $query->orWhere(function ($q) use ($group, $exceptions) {
                        // Incluye todo el grupo principal...
                        $q->where('account_sub_diary_items.code', 'like', $group . '%')
                          // ...pero excluye las excepciones para tratarlas por separado.
                          ->where(function ($sub) use ($exceptions) {
                              foreach ($exceptions as $exception) {
                                  $sub->where('account_sub_diary_items.code', 'not like', $exception . '%');
                              }
                          });
                        // Y luego vuelve a incluir explícitamente las excepciones como grupos separados.
                        foreach ($exceptions as $exception) {
                            $q->orWhere('account_sub_diary_items.code', 'like', $exception . '%');
                        }
                    });
                }
            })
            ->select(
                DB::raw('LEFT(account_sub_diary_items.code, 2) as account_group'),
                'account_sub_diary_items.code',
                'account_sub_diary_items.description',
                DB::raw('SUM(account_sub_diary_items.debit_amount) as total_debit'),
                DB::raw('SUM(account_sub_diary_items.credit_amount) as total_credit'),
                DB::raw('SUM(account_sub_diary_items.debit_amount) - SUM(account_sub_diary_items.credit_amount) as balance_debit'),
                DB::raw('SUM(account_sub_diary_items.credit_amount) - SUM(account_sub_diary_items.debit_amount) as balance_credit')
            )
            ->groupBy('account_sub_diary_items.code', 'account_sub_diary_items.description')
            ->orderBy('account_sub_diary_items.code')
            ->get();
        $pdf = Pdf::loadView('account::account_ledger.form0710', compact(
            "company",
            "sum_accounts",
            "period_id",
            "period"
        ))
            ->setPaper('a4', 'portrait');
        $filename = "Formulario_0710_{$period_id}";

        return $pdf->stream($filename . '.pdf');
    }

    public function indexAccountLedger()
    {
        return view('account::account_ledger_accounts.index');
    }

    public function exportRecords(Request $request)
    {
        $records = $this->records($request);
        $ledgerAccountTenantExport = new LedgerAccountTenantExport($records);
        return $ledgerAccountTenantExport->download('plan_de_cuentas.xlsx');
    }



    public function records(Request $request)
    {
        $code = $request->code;
        $records = DB::connection('tenant')->table('ledger_accounts_tenant')->where('active', true);
        if ($code) {
            $records->where('code', 'like', "{$code}%")
                ->orWhere('name', 'like', "%{$code}%");
        }
        $records->orderBy('code', 'asc');

        return new LedgerAccountCollection($records->paginate(30));
    }

    private function checkLedgerAccountForDesactive($code)
    {
        $exists_in_automatic = DB::connection('tenant')->table('account_sub_diary_items')->where('code', $code)->exists();
        $exists_in_sub_diary = DB::connection('tenant')->table('account_sub_diary_items')->where('code', $code)->exists();

        if ($exists_in_automatic || $exists_in_sub_diary) {
            return true;
        }
        return false;
    }

    public function desactive($code)
    {
        $is_active = $this->checkLedgerAccountForDesactive($code);
        if ($is_active) {
            return response()->json(['success' => false, 'message' => 'Cuenta contable no puede ser desactivada, ya que está siendo utilizada los asientos automáticos o plantillas.']);
        }
        $ledgerAccount = LedgerAccount::where('code', $code)->first();
        $ledgerAccount->update(['active' => false]);
        return response()->json(['success' => true, 'message' => 'Cuenta contable desactivada']);
    }

    public function active($code)
    {
        $ledgerAccount = LedgerAccount::where('code', $code)->first();
        $ledgerAccount->update(['active' => true]);
        return response()->json(['success' => true, 'message' => 'Cuenta contable activada']);
    }


    public function store(Request $request)
    {
        $code = $request->code;
        $name = strtoupper($request->name);
        $is_update = false;
        $exist = LedgerAccount::where('code', $code)->first();
        if ($exist) {
            $is_update = true;
            $exist->update([
                'code' => $code,
                'name' => $name,
            ]);
            $this->updateInAutomaticAndSubDiary($code, $name);
        } else {
            LedgerAccount::create([
                'code' => $code,
                'name' => $name,
                'active' => true,
            ]);
        }

        return response()->json([
            'message' => $is_update ? 'Cuenta contable actualizada' : 'Cuenta contable creada',
            'success' => true
        ]);
    }

    private function updateInAutomaticAndSubDiary($code, $name)
    {
        DB::connection('tenant')->table('account_sub_diary_items')->where('code', $code)->update([
            'description' => $name,
        ]);
        DB::connection('tenant')->table('account_automatic_items')->where('code', $code)->update([
            'info' => $name,
        ]);
    }
    /**
     * @param Request $request
     *
     * @return ModelTenant|\Illuminate\Database\Eloquent\Builder|Builder
     */
    public function getRetentions(Request $request)
    {


        $requestArray = $request->all();
        $date_start = $requestArray['date_start'] ?? null;
        $date_end = $requestArray['date_end'] ?? null;
        FunctionsHelper::setDateInPeriod($requestArray, $date_start, $date_end);

        $records = Retention::query();
        if ($request->has('column')) {
            $records->where($request->column, 'like', "%{$request->value}%");
        }
        $records->whereBetween('date_of_issue', [$date_start, $date_end]);

        // ->orderBy('series')
        // ->orderBy('number', 'desc');
        return $records->latest();
    }

    public function index(Request $request)
    {
        return view('account::accounting_ledger.index');
    }

    /**
     * @param Request $request
     *
     * @return Response|BinaryFileResponse
     */
    public function excel(Request $request)
    {
        $dateReport = $request->month_end;
        $records = $this->getData($request);
        $ledgerAccountExcelExport = new LedgerAccountExcelExport();

        $ledgerAccountExcelExport->setRecords($records)->setDateReport($dateReport);
        $filename = 'Libro Mayor ' . $dateReport . " - " . date('YmdHis');
        // return $ledgerAccountExcelExport->view();
        return $ledgerAccountExcelExport->download($filename . '.xlsx');
    }

    /**
     * @param Request|null $request
     *
     * @return array
     */
    protected function getData(Request $request = null)
    {
        $date = Carbon::now();
        if ($request !== null & $request->has('month_end')) {
            $dateReport = explode('-', $request->month_end);
            $date = Carbon::createFromFormat('Y-m', $dateReport[0] . "-" . $dateReport[1]);
        }

        return AccountingLedger::saveData($date);
    }

    public function record()
    {
        $months = $this->getDatesToReport();
        arsort($months);
        $data = [];

        /**
         * @var Carbon $month
         */

        foreach ($months as $month) {
            $temp = [
                'id' => $month->format('Y-m'),
                'description' => $month->format('Y-m'),
            ];
            $data[] = $temp;
        }
        return ['months' => collect($data)];
    }

    /**
     * Devuelve el rango de meses para los documentos
     *
     * @return array
     */
    protected function getDatesToReport(): array
    {

        /**
         * @var Collection $documents
         * @var Carbon     $documents_min
         * @var Carbon     $documents_max
         * @var array      $months
         */
        $documents = Document::query()->select('date_of_issue')->groupby('date_of_issue')->get();
        $documents_min = $documents->min('date_of_issue');
        $documents_max = $documents->max('date_of_issue');
        // Validar el mes actual para que no haga nada
        $months = [];
        do {
            $d = $documents_min->firstOfMonth();
            $f = $d->format('Y-m');
            $months[$f] = Carbon::createFromFormat('Y-m', $f)->firstOfMonth()->setTime(0, 0, 0);
        } while ($documents_min->addMonth() <= $documents_max);
        return $months;
    }
}

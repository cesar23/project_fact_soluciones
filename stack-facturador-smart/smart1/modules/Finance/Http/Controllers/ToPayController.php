<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Finance\Models\GlobalPayment;
use App\Models\Tenant\Cash;
use App\Models\Tenant\User;
use App\Http\Resources\Tenant\UserCollection;
use App\Models\Tenant\BankAccount;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Tenant\Company;
use Modules\Finance\Traits\FinanceTrait;
use Modules\Finance\Http\Resources\GlobalPaymentCollection;
use Modules\Finance\Exports\ToPayAllExport;
use Modules\Finance\Exports\ToPayExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Tenant\Establishment;
use Carbon\Carbon;
use App\Models\Tenant\Person;
use Modules\Dashboard\Helpers\DashboardView;
use Modules\Finance\Helpers\ToPay;
use Modules\Finance\Exports\ToPaymentMethodDayExport;
use Illuminate\Support\Facades\DB;


class ToPayController extends Controller
{

    use FinanceTrait;

    public function index()
    {

        return view('finance::to_pay.index');
    }

    public function dueToPay()
    {
        $today = Carbon::now();
        $fiveDaysFromNow = Carbon::now()->addDays(5);

        // Obtener todas las consultas y filtrar después en PHP para evitar problemas con HAVING
        $purchase_payments = DB::connection('tenant')->table('purchase_payments')
            ->select('purchase_id', DB::raw('SUM(payment) as total_payment'))
            ->groupBy('purchase_id');

        $purchases = DB::connection('tenant')
            ->table('purchases')
            ->whereIn('state_type_id', ['01', '03', '05', '07', '13'])
            ->whereIn('document_type_id', ['01', '03', 'GU75', 'NE76'])
            ->where('payment_condition_id', '01')
            ->whereNull('bill_of_exchange_pay_id')
            ->where('total', '>', 0)
            ->whereNotNull('date_of_due')
            ->join('persons', 'persons.id', '=', 'purchases.supplier_id')
            ->leftJoinSub($purchase_payments, 'payments', function ($join) {
                $join->on('purchases.id', '=', 'payments.purchase_id');
            })
            ->select(
                'purchases.id as id',
                DB::raw("DATE_FORMAT(purchases.date_of_due, '%Y-%m-%d') as date_of_due"),
                'persons.name as supplier_name',
                DB::raw("CONCAT(purchases.series,'-',purchases.number) AS number_full"),
                'purchases.total as total',
                DB::raw("IFNULL(payments.total_payment, 0) as total_payment"),
                DB::raw("'purchase' AS type")
            )
            ->where(function($query) use ($today, $fiveDaysFromNow) {
                $query->where('purchases.date_of_due', '<', $today->format('Y-m-d'))
                    ->orWhereBetween('purchases.date_of_due', [$today->format('Y-m-d'), $fiveDaysFromNow->format('Y-m-d')]);
            })
            ->get()
            ->filter(function($record) {
                return ((float)$record->total - (float)$record->total_payment) > 0;
            });

        // Obtener cuotas de compras (purchase_fee)
        $purchase_fees = DB::connection('tenant')
            ->table('purchases')
            ->join('persons', 'persons.id', '=', 'purchases.supplier_id')
            ->join('purchase_fee', 'purchases.id', '=', 'purchase_fee.purchase_id')
            ->whereIn('state_type_id', ['01', '03', '05', '07', '13'])
            ->whereIn('document_type_id', ['01', '03', '08'])
            ->whereIn('payment_condition_id', ['02', '03'])
            ->whereNotNull('purchase_fee.date')
            ->select(
                'purchase_fee.id as id',
                DB::raw("DATE_FORMAT(purchase_fee.date, '%Y-%m-%d') as date_of_due"),
                'persons.name as supplier_name',
                DB::raw("CONCAT(purchases.series,'-',purchases.number,'-',purchase_fee.id) AS number_full"),
                DB::raw("IFNULL(purchase_fee.original_amount, purchase_fee.amount) as total"),
                DB::raw("CASE WHEN purchase_fee.is_canceled = 1 THEN purchase_fee.original_amount ELSE (purchase_fee.original_amount - purchase_fee.amount) END as total_payment"),
                DB::raw("'purchase_fee' AS type"),
                'purchase_fee.is_canceled',
                'purchase_fee.amount'
            )
            ->where(function($query) use ($today, $fiveDaysFromNow) {
                $query->where('purchase_fee.date', '<', $today->format('Y-m-d'))
                    ->orWhereBetween('purchase_fee.date', [$today->format('Y-m-d'), $fiveDaysFromNow->format('Y-m-d')]);
            })
            ->get()
            ->filter(function($record) {
                return $record->is_canceled == 1 ? false : (float)$record->amount > 0;
            });

        // Letras de cambio
        $bills_of_exchanges_payments = DB::connection('tenant')
            ->table('bills_of_exchange_payments_pay')
            ->select('bill_of_exchange_id', DB::raw('SUM(payment) as total_payment'))
            ->groupBy('bill_of_exchange_id');

        $bills_of_exchange = DB::connection('tenant')
            ->table('bills_of_exchange_pay')
            ->join('persons', 'persons.id', '=', 'bills_of_exchange_pay.supplier_id')
            ->leftJoinSub($bills_of_exchanges_payments, 'payments', function ($join) {
                $join->on('bills_of_exchange_pay.id', '=', 'payments.bill_of_exchange_id');
            })
            ->whereNotNull('bills_of_exchange_pay.date_of_due')
            ->select(
                'bills_of_exchange_pay.id as id',
                DB::raw("DATE_FORMAT(bills_of_exchange_pay.date_of_due, '%Y-%m-%d') as date_of_due"),
                'persons.name as supplier_name',
                DB::raw("CONCAT(bills_of_exchange_pay.series,'-',bills_of_exchange_pay.number) AS number_full"),
                'bills_of_exchange_pay.total as total',
                DB::raw("IFNULL(payments.total_payment, 0) as total_payment"),
                DB::raw("'bill_of_exchange' AS type")
            )
            ->where(function($query) use ($today, $fiveDaysFromNow) {
                $query->where('bills_of_exchange_pay.date_of_due', '<', $today->format('Y-m-d'))
                    ->orWhereBetween('bills_of_exchange_pay.date_of_due', [$today->format('Y-m-d'), $fiveDaysFromNow->format('Y-m-d')]);
            })
            ->get()
            ->filter(function($record) {
                return ((float)$record->total - (float)$record->total_payment) > 0;
            });

        // Combinar todas las colecciones
        $records = $purchases->concat($purchase_fees)->concat($bills_of_exchange);

        // Contar deudas vencidas y por vencer
        $overdue_count = 0;
        $due_soon_count = 0;

        foreach ($records as $record) {
            $due_date = Carbon::parse($record->date_of_due);
            if ($due_date < $today) {
                $overdue_count++;
            } elseif ($due_date <= $fiveDaysFromNow) {
                $due_soon_count++;
            }
        }

        $total_count = $overdue_count + $due_soon_count;

        return response()->json([
            'success' => true,
            'data' => [
                'total_due_payments' => $total_count,
                'overdue_payments' => $overdue_count,
                'due_soon_payments' => $due_soon_count,
                'records' => $records->map(function($record) use ($today) {
                    $due_date = Carbon::parse($record->date_of_due);
                    $days_overdue = $due_date < $today ? $today->diffInDays($due_date) : 0;
                    $days_until_due = $due_date >= $today ? $today->diffInDays($due_date) : 0;

                    return [
                        'id' => $record->id,
                        'type' => $record->type,
                        'supplier_name' => $record->supplier_name,
                        'number_full' => $record->number_full,
                        'total' => number_format((float)$record->total, 2, ".", ""),
                        'total_payment' => number_format((float)$record->total_payment, 2, ".", ""),
                        'amount_due' => number_format((float)$record->total - (float)$record->total_payment, 2, ".", ""),
                        'date_of_due' => $record->date_of_due,
                        'is_overdue' => $due_date < $today,
                        'days_overdue' => $days_overdue,
                        'days_until_due' => $days_until_due
                    ];
                })->values()
            ],
            'message' => "Se encontraron {$total_count} deudas: {$overdue_count} vencidas y {$due_soon_count} por vencer en 5 días"
        ]);
    }


    public function filter()
    {

        $supplier_temp = Person::whereType('suppliers')->orderBy('name')->take(100)->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => $row->number . ' - ' . $row->name,
                'name' => $row->name,
                'number' => $row->number,
                'identity_document_type_id' => $row->identity_document_type_id,
            ];
        });
        $supplier = [];
        $supplier[] = [
            'id' => null,
            'description' => 'Todos',
            'name' => 'Todos',
            'number' => '',
            'identity_document_type_id' => '',
        ];
        $suppliers = array_merge($supplier, $supplier_temp->toArray());

        $query_users = User::all();
        if (auth()->user()->type === 'admin') {
            $newUser = new User(['id' => 0, 'name' => 'Seleccionar Todos']);
            $query_users = $query_users->add($newUser)->sortBy('id');
        }
        $users = new UserCollection($query_users);
        $establishments = [];
        $establishments[] = [
            'id' => 0,
            'name' => 'Todos',
        ];
        $establishments = collect($establishments);
        Establishment::whereActive()->get()->transform(
            function ($row)  use (&$establishments) {
                $establishments[]  = [
                    'id' => $row->id,
                    'name' => $row->description
                ];
            }
        );

        return compact('suppliers', 'establishments', 'users');
    }


    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function records(Request $request)
    {
        $data = $request->all();
        if ($request->establishment_id === 0) {
            $data['withBankLoan'] = 1;
            $data['stablishmentTopaidAll'] = 1; 
        }

        return [
            'records' => ToPay::getToPay($data)
        ];
    }

    /**
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function toPayAll()
    {

        return Excel::download(new ToPayAllExport, 'TCuentasPorPagar.xlsx');
    }


    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function toPay(Request $request)
    {

        $company = Company::first();
        $export = new ToPayExport();
        $records = ToPay::getToPay($request->all());
        $export->company($company)
            ->records($records);
        return $export->download('Reporte_Cuentas_Por_Pagar' . Carbon::now() . '.xlsx');
    }


    public function reportPaymentMethodDays(Request $request)
    {
        // 'records' => (new ToPay())->getToPay($request->all())

        $all_records = (new ToPay())->getToPay($request->all());

        $records = collect($all_records)->where('total_to_pay', '>', 0)->where('type', 'purchase')->map(function ($row) {
            $row['difference_days'] = Carbon::parse($row['date_of_issue'])->diffInDays($row['date_of_due']);
            return $row;
        });

        $company = Company::first();

        return (new ToPaymentMethodDayExport)
            ->company($company)
            ->records($records)
            ->download('Reporte_C_Pagar_F_Pago' . Carbon::now() . '.xlsx');
    }


    public function pdf(Request $request)
    {

        $records = (new ToPay())->getToPay($request->all());

        $company = Company::first();

        $pdf = PDF::loadView('finance::to_pay.report_pdf', compact("records", "company"));

        $filename = 'Reporte_Cuentas_Por_Pagar_' . date('YmdHis');

        return $pdf->download($filename . '.pdf');
    }

    public function pdf_s(Request $request)
    {
        $records = (new ToPay())->getToPay($request->all());

        $company = Company::first();

        $pdf = PDF::loadView('finance::to_pay.report_pdf_s', compact("records", "company"))
            ->setPaper('a4', 'landscape');

        $filename = 'Reporte_Cuentas_Por_Pagar_' . date('YmdHis');

        return $pdf->stream($filename . '.pdf');
    }

    public function pdf_s_especial(Request $request)
    {
        $records = ToPay::getToPay($request->all());

        $company = Company::first();

        $pdf = PDF::loadView('finance::to_pay.reports.report_pdf_s_especial', compact("records", "company"))
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isPhpEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'debugLayout' => false,
                'dpi' => 150,
                'defaultFont' => 'sans-serif',
                'enable_php' => true
            ]);

        $filename = 'Reporte_Cuentas_Por_Pagar_' . date('YmdHis');

        return $pdf->stream($filename . '.pdf');
    }

    public function eeccSupplier(Request $request)
    {
        $date = $request->date_end ?? now()->format('Y-m-d');
        $supplier_id = $request->supplier_id;

        // Obtener registros filtrados por proveedor
        $records = collect(ToPay::getToPay($request->all()))
            ->filter(function($record) use ($supplier_id) {
                return $record['supplier_id'] == $supplier_id && $record['total_to_pay'] > 0;
            })
            ->toArray();

        $bank_accounts = BankAccount::all()->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => $row->description,
                'number' => $row->number,
                'cci' => $row->cci,
            ];
        });

        $supplier = Person::whereType('suppliers')->where('id', $supplier_id)->first();
        $company = Company::first();

        $html = view('finance::to_pay.reports.eecc_supplier', compact("records", "company", "date", "supplier", "bank_accounts"))->render();

        $pdf = PDF::loadHTML($html)
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isPhpEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'debugLayout' => false,
                'dpi' => 150,
                'defaultFont' => 'sans-serif',
                'margin_bottom' => 15,
                'margin_top' => 15,
                'margin_right' => 15,
                'margin_left' => 15,
                'enable_php' => true
            ]);

        $filename = 'Reporte_Cuentas_Por_Pagar_' . date('YmdHis');
        return $pdf->stream($filename . '.pdf');
    }
}

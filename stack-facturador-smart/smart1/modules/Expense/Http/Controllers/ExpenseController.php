<?php

namespace Modules\Expense\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Expense\Models\Expense;
use Modules\Expense\Models\ExpenseReason;
use Modules\Expense\Models\ExpensePayment;
use Modules\Expense\Models\ExpenseType;
use Modules\Expense\Models\ExpenseMethodType;
use Modules\Expense\Models\ExpenseItem;
use Modules\Expense\Http\Resources\ExpenseCollection;
use Modules\Expense\Http\Resources\ExpenseResource;
use Modules\Expense\Http\Requests\ExpenseRequest;
use Illuminate\Support\Str;
use App\Models\Tenant\Person;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\CoreFacturalo\Requests\Inputs\Common\PersonInput;
use App\Models\Tenant\Establishment;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant\Company;
use Modules\Finance\Traits\FinanceTrait;
use Modules\Expense\Exports\ExpenseExport;
use Carbon\Carbon;
use App\CoreFacturalo\Helpers\Functions\GeneralPdfHelper;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\Models\Tenant\Configuration;
use App\Traits\PaymentWithNote;
use Exception;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Modules\Finance\Helpers\UploadFileHelper;

class ExpenseController extends Controller
{

    use FinanceTrait, StorageDocument, PaymentWithNote;

    public function index()
    {
        return view('expense::expenses.index');
    }


    public function create($id = null)
    {
        return view('expense::expenses.form', compact('id'));
    }

    public function columns()
    {
        return [
            'supplier_id' => 'Proveedor',
            'date_of_issue' => 'Fecha de emisión',
            'number' => 'Número',
        ];
    }


    public function records(Request $request)
    {
        $records = $this->getRecords($request->all(), Expense::class);

        return new ExpenseCollection($records->paginate(config('tenant.items_per_page')));

        /*$records = Expense::where($request->column, 'like', "%{$request->value}%")
                            ->whereTypeUser()
                            ->latest();

        return new ExpenseCollection($records->paginate(config('tenant.items_per_page')));*/
    }
    public function processEvidenciasExpense(Request $request, Expense $expense = null)
    {
        if ($request->input('temp_path')) {
            $expense = Expense::where('id', $expense->id)->first();
            $temp_path = $request->input('temp_path');

            $directory = 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'expenses' . DIRECTORY_SEPARATOR;

            $file_name_old = $request->input('image');
            $file_name_old_array = explode('.', $file_name_old);
            $file_content = file_get_contents($temp_path);
            $datenow = date('YmdHis');
            $file_name = Str::slug($expense->filename) . '-' . $datenow . '.' . $file_name_old_array[1];

            UploadFileHelper::checkIfValidFile($file_name, $temp_path, true);

            Storage::put($directory . $file_name, $file_content);
            $expense->image = $file_name;
            $expense->name_evidencia = $request->input('name_evidencia');
            $expense->save();
        }
        return $expense->getCollectionData();
        // return new ExpenseCollection($expense);


    }

    public function uploadpevidencia(Request $request)
    {

        $validate_upload = UploadFileHelper::validateUploadFile($request, 'file', 'jpg,jpeg,png,gif,svg');

        if (!$validate_upload['success']) {
            return $validate_upload;
        }

        if ($request->hasFile('file')) {
            $new_request = [
                'file' => $request->file('file'),
                'type' => $request->input('type'),
            ];

            return $this->upload_image2($new_request);
        }
        return [
            'success' => false,
            'message' =>  __('app.actions.upload.error'),
        ];
    }

    public function getRecords($request, $model)
    {

        $period = $request['period'];
        $date_start = $request['date_start'];
        $establishment_id = $request['establishment_id'];
        $date_end = $request['date_end'];
        $month_start = $request['month_start'];
        $month_end = $request['month_end'];
        $person_id = $request['person_id'];
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

        $records = $this->data($d_start, $d_end, $model, $person_id, $establishment_id);

        return $records;
    }

    private function data($date_start, $date_end, $model, $person_id = null, $establishment_id = null)
    {



        $data = $model::with('state_type')->whereBetween('date_of_issue', [$date_start, $date_end]);
        if ($establishment_id) {
            $data = $data->where('establishment_id', $establishment_id);
        }
        if ($person_id) {
            $data = $data->where('supplier_id', $person_id);
        }
        return $data->whereTypeUser()->latest();
    }


    public function tables()
    {
        $configuration = Configuration::getConfig();
        $suppliers = $this->table('suppliers');
        $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
        $currency_types = CurrencyType::whereActive()->get();
        $expense_types = ExpenseType::get();
        $expense_method_types = ExpenseMethodType::query();
        if(!$configuration->nc_payment_nv){
            $expense_method_types = $expense_method_types->where('description', '<>', 'Nota Crédito');
        }
        $expense_method_types = $expense_method_types->get();
        $expense_reasons = ExpenseReason::all();
        $payment_destinations = $this->getPaymentDestinations();
        $establishments = Establishment::where('active', true)->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => $row->description
            ];
        });

        return compact('suppliers', 'establishment', 'currency_types', 'expense_types', 'expense_method_types', 'expense_reasons', 'payment_destinations', 'establishments');
    }



    public function record($id)
    {
        $record = new ExpenseResource(Expense::findOrFail($id));

        return $record;
    }
    public function duplicate(Request $request)
    {
        // return $request->id;
        $obj = Expense::find($request->id);
        $expense = $obj->replicate();
        $expense->filename = null;
        $expense->external_id = Str::uuid()->toString();
        $expense->save();

        foreach ($obj->items as $row) {
            $new = $row->replicate();
            $new->expense_id = $expense->id;
            $new->save();
        }
        foreach ($obj->payments as $row) {
            $new = $row->replicate();
            $new->expense_id = $expense->id;
            $new->save();


            if ($row['expense_method_type_id'] == 1) {
                $row['payment_destination_id'] = 'cash';
            }

            $this->createGlobalPayment($new, $row);
        }
        $this->setFilename($expense);
        $this->createPdf($expense);

        return [
            'success' => true,
            'message' => 'Gasto duplicado con éxito',
            'data' => [
                'id' => $expense->id,
            ],
        ];
    }

    public function store(ExpenseRequest $request)
    {

        $data = self::merge_inputs($request);

        $expense =  DB::connection('tenant')->transaction(function () use ($data) {

            // $doc = Expense::create($data);
            $doc = Expense::updateOrCreate(['id' => $data['id']], $data);

            $doc->items()->delete();

            foreach ($data['items'] as $row) {
                $doc->items()->create($row);
            }

            $this->deleteAllPayments($doc->payments);

            foreach ($data['payments'] as $row) {
                $record_payment = $doc->payments()->create($row);

                if(isset($row['note_credit_id'])){
                    $this->savePaymentWithNote('expense', $record_payment->id, $row['note_credit_id'], $row['payment']);
                }

                if ($row['expense_method_type_id'] == 1 && $row['payment_destination_id'] == null) {
                    $row['payment_destination_id'] = 'cash';
                }

                $this->createGlobalPayment($record_payment, $row);
            }

            $this->setFilename($doc);
            $this->createPdf($doc);

            return $doc;
        });

        return [
            'success' => true,
            'data' => [
                'id' => $expense->id,
            ],
        ];
    }


    /**
     * 
     * Imprimir gasto
     *
     * @param  string $external_id
     * @param  string $format
     * @return mixed
     */
    public function toPrint($external_id, $format = 'a4')
    {
        $record = Expense::where('external_id', $external_id)->first();

        if (!$record) throw new Exception("El código {$external_id} es inválido, no se encontro el registro relacionado");

        // si no tienen nombre de archivo, se regulariza
        if (!$record->filename) $this->setFilename($record);

        $this->createPdf($record, $format, $record->filename);

        return GeneralPdfHelper::getPreviewTempPdf('expense', $this->getStorage($record->filename, 'expense'));
    }


    /**
     * 
     * Asignar nombre de archivo
     *
     * @param  Expense $expense
     * @return void
     */
    private function setFilename(Expense $expense)
    {
        $expense->filename = GeneralPdfHelper::getNumberIdFilename($expense->id, $expense->number);
        $expense->save();
    }


    /**
     * 
     * Crear pdf para gastos
     *
     * @param  Expense $expense
     * @param  string $format_pdf
     * @return void
     */
    public function createPdf(Expense $expense, $format_pdf = 'a4')
    {
        $file_content = GeneralPdfHelper::getBasicPdf('expense', $expense, $format_pdf);

        $this->uploadStorage($expense->filename, $file_content, 'expense');
    }


    public static function merge_inputs($inputs)
    {

        $company = Company::active();

        $values = [
            'user_id' => auth()->id(),
            'state_type_id' => $inputs['id'] ? $inputs['state_type_id'] : '05',
            'soap_type_id' => $company->soap_type_id,
            'external_id' => $inputs['id'] ? $inputs['external_id'] : Str::uuid()->toString(),
            'supplier' => PersonInput::set($inputs['supplier_id']),
        ];

        $inputs->merge($values);

        return $inputs->all();
    }

    public function table($table)
    {
        switch ($table) {
            case 'suppliers':

                $suppliers = Person::whereType('suppliers')->orderBy('name')->get()->transform(function ($row) {
                    return [
                        'id' => $row->id,
                        'description' => $row->number . ' - ' . $row->name,
                        'name' => $row->name,
                        'number' => $row->number,
                        'identity_document_type_id' => $row->identity_document_type_id,
                        'identity_document_type_code' => $row->identity_document_type->code
                    ];
                });
                return $suppliers;

                break;
            default:

                return [];

                break;
        }
    }

    public function voided($record)
    {
        try {
            $expense = Expense::findOrFail($record);
            $expense->state_type_id = 11;
            $expense->save();
            return [
                'success' => true,
                'data' => [
                    'id' => $expense->id,
                ],
                'message' => 'Gasto anulado exitosamente',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [
                    'id' => $record,
                ],
                'message' => 'Falló al anular',
            ];
        }
    }

    public function excel(Request $request)
    {

        
        $records = $this->getRecords($request->all(), Expense::class)->get();

        $establishment = auth()->user()->establishment;
        $balance = new ExpenseExport();
        $balance
            ->records($records)
            ->establishment($establishment);

        return $balance->download('Expense_' . Carbon::now() . '.xlsx');
    }

    public function pdf(Request $request)
    {

        
        $records = $this->getRecords($request->all(), Expense::class)->get();

        $establishment = auth()->user()->establishment;
        $company = Company::active();
        $company_name = $company->name;
        $company_number = $company->number;
        $pdf = PDF::loadView('expense::expenses.report_pdf', compact('records', 'establishment'))
        ->setPaper('a4', 'landscape');
    return $pdf->stream('compras.pdf');
    }

}

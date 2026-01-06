<?php

namespace App\Http\Controllers\Tenant;

use App\CoreFacturalo\Helpers\Functions\GeneralPdfHelper;
use App\CoreFacturalo\Requests\Inputs\Common\PersonInput;
use App\Exports\WarrantyDocumentExport;
use Exception;
use Illuminate\Http\Request;
use App\Models\Tenant\WarrantyDocument;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UnitTypeRequest;
use App\Http\Resources\Tenant\WarrantyDocumentCollection;
use App\Http\Resources\Tenant\WarrantyDocumentResource;
use App\Models\Tenant\Company;
use App\Models\Tenant\Person;
use App\Models\Tenant\StateType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Expense\Models\Expense;
use Illuminate\Support\Str;
use Modules\Expense\Http\Controllers\ExpenseController;
use Modules\Expense\Models\ExpenseItem;
use Modules\Expense\Models\ExpenseType;
use Modules\Finance\Traits\FinanceTrait;

class WarrantyDocumentController extends Controller
{
    use FinanceTrait;
    public   function report_index()
    {
        return view('tenant.warranty_document.report');
    }
    public   function index()
    {
        return view('tenant.warranty_document.index');
    }

    public function columns()
    {
        return [
            'between_dates' => 'Entre fechas',
            'date_of_issue' => 'Fecha',
            'customer_name' => 'Cliente',
        ];
    }



    public function filter()
    {

        $state_types = StateType::whereIn('id', ['01', '05', '09'])->get();

        return compact('state_types');
    }
    public function report_records(Request $request)
    {
        $records = WarrantyDocument::where('state', 0);
        $column = $request->column;
        $value = $request->value;

        if ($column == 'date_of_issue') {
            $records = $records->where(function ($query) use ($value) {
                $query->whereHas('sale_note', function ($query) use ($value) {
                    $query->where('date_of_issue', $value);
                })
                    ->orWhereHas('document', function ($query) use ($value) {
                        $query->where('date_of_issue', $value);
                    });
            });
        }
        if ($column == 'between_dates') {
            $end_dates = $request->end_dates;
            if ($end_dates) {
                $records = $records->where(function ($query) use ($value, $end_dates) {
                    $query->whereHas('sale_note', function ($query) use ($value, $end_dates) {
                        $query->whereBetween('date_of_issue', [$value, $end_dates]);
                    })
                        ->orWhereHas('document', function ($query) use ($value, $end_dates) {
                            $query->whereBetween('date_of_issue', [$value, $end_dates]);
                        });
                });
            }
        } else {
            $records = $records->where(function ($query) use ($value) {
                $query->whereHas('sale_note', function ($query) use ($value) {
                    $query->where('date_of_issue', $value);
                })
                    ->orWhereHas('document', function ($query) use ($value) {
                        $query->where('date_of_issue', $value);
                    });
            });
        }
        if ($column == 'customer_name') {
            $records = $records->whereHas('sale_note', function ($query) use ($value) {
                $query->whereHas('person', function ($query) use ($value) {
                    $query->where('name', 'like', "%$value%");
                });
            })
                ->orWhereHas('document', function ($query) use ($value) {
                    $query->whereHas('person', function ($query) use ($value) {
                        $query->where('name', 'like', "%$value%");
                    });
                });
        }

        return new WarrantyDocumentCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function report_records_excel(Request $request)
    {
        $records = WarrantyDocument::where('state', 0);
        $column = $request->column;
        $value = $request->value;

        if ($column == 'date_of_issue') {
            $records = $records->where(function ($query) use ($value) {
                $query->whereHas('sale_note', function ($query) use ($value) {
                    $query->where('date_of_issue', $value);
                })
                    ->orWhereHas('document', function ($query) use ($value) {
                        $query->where('date_of_issue', $value);
                    });
            });
        }
        if ($column == 'between_dates') {
            $end_dates = $request->end_dates;
            if ($end_dates) {
                $records = $records->where(function ($query) use ($value, $end_dates) {
                    $query->whereHas('sale_note', function ($query) use ($value, $end_dates) {
                        $query->whereBetween('date_of_issue', [$value, $end_dates]);
                    })
                        ->orWhereHas('document', function ($query) use ($value, $end_dates) {
                            $query->whereBetween('date_of_issue', [$value, $end_dates]);
                        });
                });
            }
        } else {
            $records = $records->where(function ($query) use ($value) {
                $query->whereHas('sale_note', function ($query) use ($value) {
                    $query->where('date_of_issue', $value);
                })
                    ->orWhereHas('document', function ($query) use ($value) {
                        $query->where('date_of_issue', $value);
                    });
            });
        }
        if ($column == 'customer_name') {
            $records = $records->whereHas('sale_note', function ($query) use ($value) {
                $query->whereHas('person', function ($query) use ($value) {
                    $query->where('name', 'like', "%$value%");
                });
            })
                ->orWhereHas('document', function ($query) use ($value) {
                    $query->whereHas('person', function ($query) use ($value) {
                        $query->where('name', 'like', "%$value%");
                    });
                });
        }
        $data = $records->get()->transform(function($row){
            $document = $row->document ?? $row->sale_note;
            $document_type_id = $document->document_type_id;
            $document_type = "NOTA DE VENTA";
            if($document_type_id == '01') {
                $document_type = "FACTURA";
            }
            if($document_type_id == '03') {
                $document_type = "BOLETA";
            }
            $quantity = intval($row->quantity);
            return [
                'id' => $row->id,
                'document_type' => $document_type,
                'date_of_issue' => $document->date_of_issue->format('Y-m-d'),
                'series' => $document->series,
                'number' => $document->number,
                'customer_name' => $document->customer->name,
                'quantity' => $quantity,
                'total_returned' => $row->state ? 0 : $quantity,
                'debit' =>  $row->state ? $quantity : 0,
                'total' => $row->total,
                'state' => (bool)$row->state,
            
            ];
        });
        
        $warranty_document_export = new WarrantyDocumentExport();
            return  $warranty_document_export->records($data)
            ->company(Company::active())
            ->download('Reporte_Devoluciones_' . Carbon::now() . '.xlsx');
    }
    public function records(Request $request)
    {
        $records = WarrantyDocument::query();

        $column = $request->column;
        $value = $request->value;

        if ($column == 'date_of_issue') {
            $records = $records->where(function ($query) use ($value) {
                $query->whereHas('sale_note', function ($query) use ($value) {
                    $query->where('date_of_issue', $value);
                })
                    ->orWhereHas('document', function ($query) use ($value) {
                        $query->where('date_of_issue', $value);
                    });
            })->orWhere('date_cancel', $value);
        }
        if ($column == 'between_dates') {
            $end_dates = $request->end_dates;
            if ($end_dates) {
                $records = $records->where(function ($query) use ($value, $end_dates) {
                    $query->whereHas('sale_note', function ($query) use ($value, $end_dates) {
                        $query->whereBetween('date_of_issue', [$value, $end_dates]);
                    })
                        ->orWhereHas('document', function ($query) use ($value, $end_dates) {
                            $query->whereBetween('date_of_issue', [$value, $end_dates]);
                        });
                })->orWhereBetween('date_cancel', [$value, $end_dates]);
            }
        } else {
            $records = $records->where(function ($query) use ($value) {
                $query->whereHas('sale_note', function ($query) use ($value) {
                    $query->where('date_of_issue', $value);
                })
                    ->orWhereHas('document', function ($query) use ($value) {
                        $query->where('date_of_issue', $value);
                    });
            })->orWhere('date_cancel', $value);
        }
        if ($column == 'customer_name') {
            $records = $records->whereHas('sale_note', function ($query) use ($value) {
                $query->whereHas('person', function ($query) use ($value) {
                    $query->where('name', 'like', "%$value%");
                });
            })
                ->orWhereHas('document', function ($query) use ($value) {
                    $query->whereHas('person', function ($query) use ($value) {
                        $query->where('name', 'like', "%$value%");
                    });
                });
        }

        return new WarrantyDocumentCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function record($id)
    {
        $record = new WarrantyDocumentResource(WarrantyDocument::findOrFail($id));

        return $record;
    }
    public function return_warranty($warranty_document_id)
    {

        try {
            DB::connection('tenant')->beginTransaction();
            $warranty_document = WarrantyDocument::findOrFail($warranty_document_id);
            $customer_id =  null;
            if ($warranty_document->sale_note) {
                $customer_id = $warranty_document->sale_note->customer_id;
            }else{
                $customer_id = $warranty_document->document->customer_id;
            }
            $customer = Person::find($customer_id);
            //check if exists a supplier with same name and same number in persons table
            $supplier = Person::where('name', $customer->name)
                ->where('number', $customer->number)
                ->where('type', 'suppliers')
                ->first();
            //if not exists create a new supplier cloning the customer
            if (!$supplier) {
                $supplier = $customer->replicate();
                $supplier->type = 'suppliers';
                $supplier->save();
            }
            $supplier_id = $supplier->id;
            
            $expense_type = ExpenseType::where('description', 'DEVOLUCION DE BOTELLAS')->first();
            if (!$expense_type) {
                $expense_type = new ExpenseType;
                $expense_type->description = 'DEVOLUCION DE BOTELLAS';
                $expense_type->save();
            }

            $supplier_id = $supplier_id;
            $company = Company::active();
            $expense = new Expense;
            $expense->user_id = auth()->id();
            $expense->state_type_id =  '05';
            $expense->soap_type_id = $company->soap_type_id;
            $expense->external_id =  Str::uuid()->toString();
            $expense->supplier = PersonInput::set($supplier_id);
            $expense->expense_type_id = $expense_type->id;
            $expense->establishment_id = auth()->user()->establishment_id;
            $expense->supplier_id = $supplier_id;
            $expense->expense_reason_id = 1;
            $expense->currency_type_id = "PEN";
            $expense->date_of_issue = Carbon::now();
            $expense->time_of_issue = Carbon::now();
            $expense->exchange_rate_sale = 1;
            $expense->total = $warranty_document->total;
            $expense->save();
            $payment = [
                'date_of_payment' => Carbon::now(),
                'expense_method_type_id' => 1,
                'payment' => $warranty_document->total,
            ];
            $record_payment = $expense->payments()->create($payment);

            if ($payment['expense_method_type_id'] == 1) {
                $row['payment_destination_id'] = 'cash';
            }

            $this->createGlobalPayment($record_payment, $row);
            // $expense_controller = new ExpenseController;
            $this->setFilename($expense);
            (new ExpenseController)->createPdf($expense);

            $expense_item = new ExpenseItem;
            $expense_item->expense_id = $expense->id;
            $expense_item->description = 'DEVOLUCION DE BOTELLAS';
            $expense_item->total = $warranty_document->total;
            $expense_item->save();

            $warranty_document->state = 0;
            $warranty_document->date_cancel = Carbon::now();
            $warranty_document->expense_id = $expense->id;
            $warranty_document->save();
            DB::connection('tenant')->commit();

            return [
                'success' => true,
                'message' => 'Devolución realizada con éxito'
            ];
        } catch (Exception $e) {
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    private function setFilename(Expense $expense)
    {
        $expense->filename = GeneralPdfHelper::getNumberIdFilename($expense->id, $expense->number);
        $expense->save();
    }
    public function store(UnitTypeRequest $request)
    {
        $id = $request->input('id');
        $unit_type = WarrantyDocument::firstOrNew(['id' => $id]);
        $unit_type->fill($request->all());
        $unit_type->save();

        return [
            'success' => true,
            'message' => ($id) ? 'Unidad editada con éxito' : 'Unidad registrada con éxito'
        ];
    }

    public function destroy($id)
    {
        try {

            $record = WarrantyDocument::findOrFail($id);
            $record->delete();

            return [
                'success' => true,
                'message' => 'Unidad eliminada con éxito'
            ];
        } catch (Exception $e) {

            return ($e->getCode() == '23000') ? ['success' => false, 'message' => 'La unidad esta siendo usada por otros registros, no puede eliminar'] : ['success' => false, 'message' => 'Error inesperado, no se pudo eliminar la unidad'];
        }
    }
}

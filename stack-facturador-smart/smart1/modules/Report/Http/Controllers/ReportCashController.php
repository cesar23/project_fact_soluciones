<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Cash;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Report\Exports\DocumentExport;
use Illuminate\Http\Request;
use Modules\Report\Traits\ReportTrait;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\User;
use App\Models\Tenant\Document;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\DocumentPayment;
use App\Models\Tenant\Item;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\Quotation;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\SaleNotePayment;
use App\Services\DocumentProcessors\DocumentProcessorFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\Finance\Models\GlobalPayment;
use App\Traits\ReportCashTrait;
use Mpdf\Mpdf;
use App\Services\DocumentProcessors\CashTransactionProcessor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Modules\Report\Http\Resources\CashClosureCollection;
use Modules\Sale\Models\QuotationPayment;

class ReportCashController extends Controller
{
    use ReportTrait, ReportCashTrait;


    public function filter()
    {

        $document_types = [
            ['id' => '01', 'description' => 'TODOS LOS COMPROBANTES'],
            ['id' => '02', 'description' => 'FACTURA - BOLETA DE VENTA ELECTRÓNICA'],
            ['id' => '03', 'description' => 'NOTAS DE VENTA'],
        ];

        $users = User::get(['id', 'name']);

        return compact('document_types', 'users');
    }


    public function index()
    {

        return view('report::cash.index');
    }

    public function records(Request $request)
    {
        // $records = $this->getRecordsCash($request->all());
        $records = $this->getRecords($request->all());
        // dd($records);
        return new CashClosureCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function getRecords($request)
    {
        $date_start = isset($request['date_start']) ? $request['date_start'] : null;
        $date_end = isset($request['date_end']) ? $request['date_end'] : null;
        $records = Cash::select('user_id', DB::raw('MAX(id) as id'), DB::raw('SUM(final_balance) as total_balance'), DB::raw('COUNT(id) as number_closures'));

        if ($date_start && $date_end) {
            $records = $records->whereBetween('date_opening', [$date_start, $date_end]);
        } else if ($date_start) {
            $records = $records->where('date_opening', '>=', $date_start);
        } else if ($date_end) {
            $records = $records->where('date_opening', '<=', $date_end);
        }

        $records = $records->with('user')  // Cargar relación de usuario si la necesitas
            ->groupBy('user_id')
            ->orderBy('user_id');

        return $records;
    }
    private function getCashIdByDate($request)
    {
        $date_start = isset($request['date_start']) ? $request['date_start'] : null;
        $date_end = isset($request['date_end']) ? $request['date_end'] : null;

        $query = Cash::select('id', 'user_id');

        if ($date_start && $date_end) {
            $query->whereBetween('date_opening', [$date_start, $date_end]);
        } elseif ($date_start) {
            $query->where('date_opening', '>=', $date_start);
        } elseif ($date_end) {
            $query->where('date_opening', '<=', $date_end);
        }

        $records = $query->get();

        $grouped = $records->groupBy('user_id')->map(function ($items) {
            return $items->pluck('id')->toArray();
        });

        return $grouped->toArray();
    }
    private function getCashIdByUserAndDate($request)
    {
        $date_start = isset($request['date_start']) ? $request['date_start'] : null;
        $date_end = isset($request['date_end']) ? $request['date_end'] : null;
        $user_id = isset($request['user_id']) ? $request['user_id'] : null;
        $records = Cash::select('id');



        if ($user_id) {
            $records = $records->where('user_id', $user_id);
        }
        if ($date_start && $date_end) {
            $records = $records->whereBetween('date_opening', [$date_start, $date_end]);
        } else if ($date_start) {
            $records = $records->where('date_opening', '>=', $date_start);
        } else if ($date_end) {
            $records = $records->where('date_opening', '<=', $date_end);
        }

        return $records->get()->pluck('id')->toArray();
        // return [3];
    }

    public function getCashClosuresByUserAndDate(Request $request)
    {
        // Verificar si se requiere procesamiento en segundo plano
        $cash_ids = $this->getCashIdByUserAndDate($request);
        if (empty($cash_ids)) {
            return response()->json(['error' => 'No se encontraron datos para generar el reporte'], 404);
        }

        $first_id = $cash_ids[0];
        $last_id = end($cash_ids);
        $first_cash = Cash::select('date_opening', 'date_closed', 'time_opening', 'time_closed')->find($first_id);
        $last_cash = Cash::select('date_opening', 'date_closed', 'time_opening', 'time_closed')->find($last_id);

        $cash_general_info = [
            'date_start' => $first_cash->date_opening,
            'date_end' => $last_cash->date_closed,
            'time_start' => $first_cash->time_opening,
            'time_end' => $last_cash->time_closed,
        ];

        reset($cash_ids);

        $combined_data = null;

        foreach ($cash_ids as $id) {
            $current_data = $this->setDataToReport($id, true);
            if (!$combined_data) {
                $combined_data = $current_data;
            } else {
                $this->combineNumericValues($combined_data, $current_data);
                $this->combineCollections($combined_data, $current_data);
                $this->combinePaymentMethods($combined_data, $current_data);
            }
        }

        if (!$combined_data) {
            return response()->json(['error' => 'No se encontraron datos para generar el reporte'], 404);
        }
        return $this->generatePdfForCombinedData($combined_data, $request, $cash_general_info);
    }

    public function getCashClosuresByDate(Request $request)
    {
        // Verificar si se requiere procesamiento en segundo plano
        $cash_ids = $this->getCashIdByDate($request);
        $all_combined_data = [];

        foreach ($cash_ids as $user_id => $cash_ids) {
            if (empty($cash_ids)) {
                return response()->json(['error' => 'No se encontraron datos para generar el reporte'], 404);
            }

            $first_id = $cash_ids[0];
            $last_id = end($cash_ids);
            $first_cash = Cash::select('date_opening', 'date_closed', 'time_opening', 'time_closed')->find($first_id);
            $last_cash = Cash::select('date_opening', 'date_closed', 'time_opening', 'time_closed')->find($last_id);

            $cash_general_info = [
                'date_start' => $first_cash->date_opening,
                'date_end' => $last_cash->date_closed,
                'time_start' => $first_cash->time_opening,
                'time_end' => $last_cash->time_closed,
            ];

            reset($cash_ids);

            $combined_data = null;

            foreach ($cash_ids as $id) {
                $current_data = $this->setDataToReport($id, true, true);

                if (!$combined_data) {
                    $combined_data = $current_data;
                } else {
                    $this->combineNumericValues($combined_data, $current_data);
                    $this->combineCollections($combined_data, $current_data);
                    $this->combinePaymentMethods($combined_data, $current_data);
                }
            }
            $combined_data['user_id'] = $user_id;
            $combined_data['cash_general_info'] = $cash_general_info;

            $all_combined_data[] = $combined_data;
        }


        if (!$all_combined_data) {
            return response()->json(['error' => 'No se encontraron datos para generar el reporte'], 404);
        }

        // return $all_combined_data;



        return $this->generatePdfAllForCombinedData($all_combined_data, $request);
    }

    private function generatePdfAllForCombinedData($all_combined_data, $request)
    {
        $company = Company::first();
        $company_name = $company->name;
        $company_number = $company->number;
        $establishment = Establishment::find(auth()->user()->establishment_id);
        $establishment_description = $establishment->description;

        // Configurar MPdf con opciones optimizadas y en formato horizontal (landscape)
        $pdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L', // A4 Landscape (horizontal)
            'margin_top' => 10,
            'margin_right' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'tempDir' => storage_path('app/pdf_temp'),
            'fontCache' => true,
            'useSubstitutions' => false,
            'simpleTables' => true,
            'use_kwt' => false,
        ]);
        foreach ($all_combined_data as $key => $combined_data) {
            $all_combined_data[$key]['methods_payment'] = array_filter($combined_data['methods_payment'], function ($method) {
                return isset($method['sum']) && (float)str_replace(',', '', $method['sum']) > 0;
            });

            // No eliminamos los documentos para mantener la información de los vendedores
            // Pero verificamos si está establecido para evitar errores
            if (!isset($all_combined_data[$key]['all_documents'])) {
                $all_combined_data[$key]['all_documents'] = [];
            }

            // Si está como Collection, convertir a array para facilitar procesamiento
            if (is_object($all_combined_data[$key]['all_documents']) && method_exists($all_combined_data[$key]['all_documents'], 'toArray')) {
                $all_combined_data[$key]['all_documents'] = $all_combined_data[$key]['all_documents']->toArray();
            }
        }

        // Datos para la vista
        $pdf_data = [
            'all_combined_data' => $all_combined_data,
            'company_name' => $company_name,
            'company_number' => $company_number,
            'establishment_description' => $establishment_description,
            'date_start' => $request->input('date_start'),
            'date_end' => $request->input('date_end')
        ];
        // Renderizar y generar PDF
        $html = view('report::cash.report_closure_user_all', $pdf_data)->render();
        $pdf->WriteHTML($html);

        // Nombre del archivo
        $filename = 'Reporte_Caja_Consolidado_' . date('YmdHis') . '.pdf';

        // Devolver el PDF como descarga
        return response()->make(
            $pdf->output('', 'S'),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"'
            ]
        );
    }

    /**
     * Genera un PDF con los datos combinados
     */
    private function generatePdfForCombinedData($data, $request, $cash_general_info)
    {
        $company = Company::first();
        $establishment = Establishment::find(auth()->user()->establishment_id);

        // Filtrar métodos de pago
        $data['methods_payment'] = array_filter($data['methods_payment'], function ($method) {
            return isset($method['sum']) && (float)str_replace(',', '', $method['sum']) > 0;
        });
        $data['all_documents'] = [];

        // Configurar MPdf con opciones optimizadas y en formato horizontal (landscape)
        $pdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L', // A4 Landscape (horizontal)
            'margin_top' => 10,
            'margin_right' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'tempDir' => storage_path('app/pdf_temp'),
            'fontCache' => true,
            'useSubstitutions' => false,
            'simpleTables' => true,
            'use_kwt' => false,
        ]);

        // Datos para la vista
        $pdf_data = [
            'company' => $company,
            'data' => $data,
            'cash_general_info' => $cash_general_info,
            'company' => $company,
            'establishment' => $establishment,
            'date_start' => $request->input('date_start'),
            'date_end' => $request->input('date_end'),
            'user_name' => isset($request['user_id']) ? User::find($request['user_id'])->name : 'Todos los usuarios'
        ];

        // Renderizar y generar PDF
        $html = view('report::cash.report_closure_user', $pdf_data)->render();
        $pdf->WriteHTML($html);

        // Nombre del archivo
        $filename = 'Reporte_Caja_Consolidado_' . date('YmdHis') . '.pdf';

        // Devolver el PDF como descarga
        return response()->make(
            $pdf->output('', 'S'),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"'
            ]
        );
    }

    /**
     * Combina valores numéricos sumándolos
     */
    private function combineNumericValues(&$target, $source)
    {
        // Lista de claves numéricas a sumar
        $numeric_keys = [
            'cash_income', 'cash_egress', 'final_balance', 'nota_venta',
            'total_tips', 'total_payment_cash_01_document',
            'total_payment_cash_01_sale_note', 'total_cash_payment_method_type_01',
            'total_cash_income_pmt_01', 'total_cash_egress_pmt_01',
            'document_credit_total', 'total_virtual', 'total_efectivo',
            'nota_credito', 'nota_debito', 'items', 'cpe_total', 'cpe_total_cash', 'quotations_total',
            'sale_notes_total', 'sale_notes_total_cash', 'total_cash_efectivo',
            'total_bank', 'total_credit', 'cash_beginning_balance',
            'total_gain_items'
        ];

        foreach ($numeric_keys as $key) {

            if (isset($target[$key]) && isset($source[$key])) {
                // Si son strings formatteados con número, convertir a float primero
                if (is_string($target[$key])) {
                    $target[$key] = (float) str_replace(',', '', $target[$key]);
                }

                if (is_string($source[$key])) {
                    $source_value = (float) str_replace(',', '', $source[$key]);
                } else {
                    $source_value = $source[$key];
                }

                $target[$key] += $source_value;

                // Si originalmente era string formateado, volvemos a formatearlo
                if (is_string($target[$key])) {
                    $target[$key] = self::FormatNumber($target[$key]);
                }
            }
        }
    }

    /**
     * Combina colecciones de documentos
     */
    private function combineCollections(&$target, $source)
    {
        $collection_keys = [
            'all_documents', 'collection_items', 'all_items', 'document_credit', 'sellers'
        ];

        foreach ($collection_keys as $key) {
            if (isset($target[$key]) && isset($source[$key])) {
                if (is_array($target[$key])) {
                    $target[$key] = array_merge($target[$key], $source[$key]);
                } elseif (is_object($target[$key]) && method_exists($target[$key], 'concat')) {
                    $target[$key] = $target[$key]->concat($source[$key]);
                }
            } elseif (!isset($target[$key]) && isset($source[$key])) {
                // Si la clave no existe en el target pero sí en source, la copiamos
                $target[$key] = $source[$key];
            }
        }

        // Quitar duplicados después de combinar
        if (isset($target['all_documents'])) {
            $target['all_documents'] = $this->removeDuplicates($target['all_documents']);
        }
    }

    /**
     * Combina los métodos de pago sumando sus montos
     */
    private function combinePaymentMethods(&$target, $source)
    {
        if (!isset($target['methods_payment']) || !isset($source['methods_payment'])) {
            return;
        }

        // Crear un mapa de payment_method_type_id => índice para facilitar la búsqueda
        $payment_method_map = [];
        foreach ($target['methods_payment'] as $index => $method) {
            if (isset($method['payment_method_type_id'])) {
                $payment_method_map[$method['payment_method_type_id']] = $index;
            }
        }

        // Combinar los métodos de pago
        foreach ($source['methods_payment'] as $source_method) {
            $payment_id = $source_method['payment_method_type_id'] ?? null;

            if ($payment_id && isset($payment_method_map[$payment_id])) {
                // El método ya existe, sumamos el monto
                $target_index = $payment_method_map[$payment_id];
                $target_sum = (float) str_replace(',', '', $target['methods_payment'][$target_index]['sum']);
                $source_sum = (float) str_replace(',', '', $source_method['sum']);

                $target['methods_payment'][$target_index]['sum'] = self::FormatNumber($target_sum + $source_sum);
            } else {
                // Nuevo método, lo añadimos
                $target['methods_payment'][] = $source_method;

                // Actualizar el mapa
                if ($payment_id) {
                    $payment_method_map[$payment_id] = count($target['methods_payment']) - 1;
                }
            }
        }

        // Reordenar índices de iteración
        foreach ($target['methods_payment'] as $index => &$method) {
            $method['iteracion'] = $index + 1;
        }
    }

    public function pdf(Request $request)
    {

        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;
        $records = $this->getRecords($request->all(), Document::class)->get();

        $pdf = PDF::loadView('report::documents.report_pdf', compact("records", "company", "establishment"));

        $filename = 'Reporte_Ventas_' . date('YmdHis');

        return $pdf->download($filename . '.pdf');
    }




    public function excel(Request $request)
    {

        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;

        $records = $this->getRecords($request->all(), Document::class)->get();

        return (new DocumentExport)
            ->records($records)
            ->company($company)
            ->establishment($establishment)
            ->download('Reporte_Ventas_' . Carbon::now() . '.xlsx');
    }


    public function setDataToReport($cash_id = 0, $withBank = false, $withGainItems = false)
    {
        Log::info("setDataToReport");
        set_time_limit(0);

        $cash = Cash::findOrFail($cash_id);

        $data = $this->initializeData($cash);
        $methods_payment = $this->initializePaymentMethods();
        $status_type_id = self::getStateTypeId();

        $credit_documents = $this->getCreditDocuments($cash_id);
        $notes_credit = $this->getNotes($cash_id, '07');
        $notes_debit = $this->getNotes($cash_id, '08');
        $data['document_credit'] = $credit_documents['documents']->concat($credit_documents['sale_notes']);
        $data['document_credit_total'] = $data['document_credit']->sum('total');
        $data['nota_credito'] = $notes_credit['documents']->sum('total');
        $data['nota_debito'] = $notes_debit['documents']->sum('total');

        $cash_documents = $this->getCashDocuments($cash_id);

        if ($withBank) {
            // $cash_documents = $cash_documents->concat($this->addBankDocuments($cash_documents, $cash_id));
            $cash_documents = $this->addBankDocuments($cash_documents, $cash_id);
        }

        $result = $this->processDocuments($cash_documents, $status_type_id, $methods_payment, $withGainItems);

        // Obtener y procesar TODOS los documentos por cash_id, independientemente de los pagos
        $all_documents = $this->getAllDocumentsByCash($cash_id);
        $this->processAllDocumentsByCash($all_documents, $result, $status_type_id, $withGainItems,$cash_id);

        $data = array_merge($data, $result);
        $data['methods_payment'] = $this->formatPaymentMethods($methods_payment);
        $data['total_virtual'] = $this->calculateVirtualTotal($data['methods_payment']);
        $data['total_bank'] = $this->calculateBankTotal($data['methods_payment']);
        $data['total_cash_efectivo'] = $this->calculateCashTotal($data['methods_payment']);

        $data['total_credit'] = $this->calculateCreditTotal($data['methods_payment']);
        $data = $this->calculateFinalBalances($data);

        $data["all_documents"] = $this->removeDuplicates($data["all_documents"]);

        // Log::info($tmp_gain);
        // $numbers = collect($data["all_documents"])->values('number')->all();
        $data['nota_credito'] = $notes_credit['documents']->sum('total');
        $data['nota_debito'] = $notes_debit['documents']->sum('total');

        return $data;
    }

    private function calculateNotaDebito($all_documents)
    {
        return $all_documents->where('document_type_id', '08')->sum('total');
    }

    private function calculateNotaCredito($all_documents)
    {
        return $all_documents->where('document_type_id', '07')->sum('total');
    }

    private function initializeData($cash)
    {
        return [
            'counter' => $cash->counter,
            'document_credit_total' => 0,
            'cash' => $cash,
            'cash_user_name' => $cash->user->name,
            'cash_date_opening' => $cash->date_opening,
            'cash_state' => $cash->state,
            'cash_date_closed' => $cash->date_closed,
            'cash_time_closed' => $cash->time_closed,
            'cash_time_opening' => $cash->time_opening,
            'company_name' => Company::first()->name,
            'company_number' => Company::first()->number,
            'establishment_description' => $cash->user->establishment->description,
            'nota_venta' => 0,
            'total_payment_cash_01_document' => 0,
            'total_payment_cash_01_sale_note' => 0,
            'total_cash_payment_method_type_01' => 0,
            'separate_cash_transactions' => Configuration::getSeparateCashTransactions(),
            'total_cash_income_pmt_01' => 0,
            'total_cash_egress_pmt_01' => 0,
        ];
    }

    private function initializePaymentMethods()
    {
        return cache()->remember('payment_methods', 60, function () {
            return collect(PaymentMethodType::all())->transform(function ($row) {
                return (object)[
                    'id' => $row->id,
                    'name' => $row->description,
                    'description' => $row->description,
                    'is_credit' => $row->is_credit,
                    'is_cash' => $row->is_cash,
                    'is_digital' => $row->is_digital,
                    'is_bank' => $row->is_bank,
                    'sum' => 0,
                ];
            });
        });
    }

    private function getCashDocuments($cash_id)
    {
        // Obtenemos los tipos de pagos que necesitamos procesar
        $payment_types = [
            'App\Models\Tenant\DocumentPayment',
            'App\Models\Tenant\SaleNotePayment',
            'Modules\Sale\Models\QuotationPayment',
            'Modules\Expense\Models\ExpensePayment',
            'App\Models\Tenant\PackageHandlerPayment',
            'Modules\Finance\Models\IncomePayment',
            'App\Models\Tenant\PurchasePayment',
            'App\Models\Tenant\TechnicalServicePayment'
        ];

        // Consulta básica con solo los datos necesarios
        return GlobalPayment::select([
            'id', 'destination_id', 'destination_type',
            'payment_id', 'payment_type'
        ])
            ->where('destination_id', $cash_id)
            ->where('destination_type', Cash::class)
            ->whereIn('payment_type', $payment_types)
            ->get();
    }

    private function addDocumentsThatNotIncludeInGlobalPayment($cash_documents, $cash_id)
    {
        // Obtener documentos que tienen el cash_id especificado
        $documents_with_cash_id = Document::with(['payments', 'document_type', 'person'])
            ->where('cash_id', $cash_id)
            ->whereIn('state_type_id', self::getStateTypeId())
            
            ->get();

        // Obtener notas de venta que tienen el cash_id especificado
        $sale_notes_with_cash_id = SaleNote::with(['payments', 'person'])
            ->where('cash_id', $cash_id)
            ->whereIn('state_type_id', self::getStateTypeId())
            
            ->get();

        $quotations_with_cash_id = Quotation::with(['payments', 'person'])
            ->where('cash_id', $cash_id)
            ->whereIn('state_type_id', self::getStateTypeId())
            ->get();

        // Filtrar para obtener solo documentos cuyos pagos no estén en GlobalPayment
        $documents_not_in_global_payment = collect();

        foreach ($documents_with_cash_id as $document) {
            foreach ($document->payments as $payment) {
                if (!$payment->global_payment) {
                    // Si el pago no tiene entrada en global_payment, lo añadimos
                    $documents_not_in_global_payment->push((object)[
                        'payment_id' => $payment->id,
                        'payment_type' => 'App\Models\Tenant\DocumentPayment',
                    ]);
                }
            }
        }

        foreach ($sale_notes_with_cash_id as $sale_note) {
            foreach ($sale_note->payments as $payment) {
                if (!$payment->global_payment) {
                    // Si el pago no tiene entrada en global_payment, lo añadimos
                    $documents_not_in_global_payment->push((object)[
                        'payment_id' => $payment->id,
                        'payment_type' => 'App\Models\Tenant\SaleNotePayment',
                    ]);
                }
            }
        }

        foreach ($quotations_with_cash_id as $quotation_payment) {
            foreach ($quotation_payment->payments as $payment) {
                if (!$payment->global_payment) {
                    $documents_not_in_global_payment->push((object)[
                        'payment_id' => $payment->id,
                        'payment_type' => 'Modules\Sale\Models\QuotationPayment',
                    ]);
                }
            }
        }


        // Combinar los documentos encontrados con la colección original
        return $cash_documents->concat($documents_not_in_global_payment);
    }
    private function addBankDocuments($cash_documents, $cash_id)
    {
        $bank_documents = DocumentPayment::with([
            'document' => function ($query) {
                $query->withoutGlobalScopes();
            },
            'document.items',
            'document.document_type',
            'document.person',
            'global_payment'
        ])
            ->whereHas('global_payment', function ($query) {
                $query->where('destination_type', 'App\Models\Tenant\BankAccount');
            })
            ->where(function ($query) use ($cash_id) {
                $query->where('cash_id', $cash_id)
                    ->orWhereHas('document', function ($q) use ($cash_id) {
                        $q->where('cash_id', $cash_id);
                    });
            })->get()->transform(function ($item) {
                return (object)[
                    'payment_id' => $item->id,
                    'payment_type' => 'App\Models\Tenant\DocumentPayment',
                ];
            });

        $bank_sale_notes = SaleNotePayment::with([
            'sale_note' => function ($query) {
                $query->withoutGlobalScopes();
            },
            'sale_note.items',
            'sale_note.person',
            'global_payment'
        ])
            ->whereHas('global_payment', function ($query) {
                $query->where('destination_type', 'App\Models\Tenant\BankAccount');
            })
            ->where(function ($query) use ($cash_id) {
                $query->where('cash_id', $cash_id)
                    ->orWhereHas('sale_note', function ($q) use ($cash_id) {
                        $q->where('cash_id', $cash_id);
                    });
            })
            ->get()->transform(function ($item) {
                return (object)[
                    'payment_id' => $item->id,
                    'payment_type' => 'App\Models\Tenant\SaleNotePayment',
                ];
            });

        $quotation_payments = QuotationPayment::with([
            'quotation' => function ($query) {
                $query->withoutGlobalScopes();
            },
            'quotation.items',
            'quotation.person',
            'global_payment'
        ])
            ->whereHas('global_payment', function ($query) {
                $query->where('destination_type', 'App\Models\Tenant\BankAccount');
            })
            ->where(function ($query) use ($cash_id) {
                $query->where('cash_id', $cash_id)
                    ->orWhereHas('quotation', function ($q) use ($cash_id) {
                        $q->where('cash_id', $cash_id);
                    });
            })->get()->transform(function ($item) {
                return (object)[
                    'payment_id' => $item->id,
                    'payment_type' => 'Modules\Sale\Models\QuotationPayment',
                ];
            });

        return $cash_documents->concat($bank_documents)->concat($bank_sale_notes)->concat($quotation_payments);
    }

    private function processDocuments($cash_documents, $status_type_id, &$methods_payment, $withGainItems = false)
    {
        $result = [
            'all_documents' => collect(),
            'collection_items' => collect(),
            'cash_income' => 0,
            'cash_egress' => 0,
            'final_balance' => 0,
            'nota_credito' => 0,
            'nota_debito' => 0,
            'total_tips' => 0,
            'sale_notes_total' => 0,
            'sale_notes_total_cash' => 0,
            'cpe_total' => 0,
            'cpe_total_cash' => 0,
            'quotations_total' => 0,
            'items' => 0,
            'all_items' => collect(),
            'cpe_n' => [],
            'total_gain_items' => 0,
        ];
        $cash_id = null;
        // Añadir documentos cuyos pagos no están en GlobalPayment
        $cash_document = isset($cash_documents[0]) ? $cash_documents[0] : null;
        if (isset($cash_document->destination_id)) {
            $cash_id = $cash_document->destination_id;
        }
        if ($cash_id) {
            $cash_documents = $this->addDocumentsThatNotIncludeInGlobalPayment($cash_documents, $cash_id);
        }

        // Agrupamos por tipo de pago para procesarlos en lotes
        $grouped_documents = $cash_documents->groupBy('payment_type');

        foreach ($grouped_documents as $payment_type => $documents) {
            $processor = $this->getDocumentProcessor($payment_type);
            if (!$processor) continue;

            // Extraemos los IDs para cargar los datos en lote
            $payment_ids = $documents->pluck('payment_id')->toArray();

            // Procesamos cada tipo de pago con sus propios métodos optimizados
            $processed_data = $processor->processBatch($payment_ids, $status_type_id, $methods_payment, $result, $withGainItems);

            if (!empty($processed_data)) {
                // $result['all_documents'] = $result['all_documents']->concat($processed_data);
            }
            // Log::info(json_encode($methods_payment));
        }
        $result['total_tips'] = $result['all_documents']->sum('total_tips');
        if ($withGainItems) {
            $items_unique_ids = $result['collection_items']->unique('item_id')->values()->pluck('item_id')->toArray();
            $purchase_prices = DB::connection('tenant')->table('items')
                ->select('id', 'purchase_unit_price')
                ->whereIn('id', $items_unique_ids)
                ->pluck('purchase_unit_price', 'id')
                ->toArray();
            $collection_items_array = $result['collection_items']->toArray();
            foreach ($collection_items_array as &$item) {
                $item['purchase_unit_price'] = floatval($purchase_prices[$item['item_id']] ?? 0);
            }
            $result['collection_items'] = collect($collection_items_array);
            $result['total_gain_items'] = $this->calculateTotalGainItems($result['collection_items']);
        }
    
        return $result;
    }

    private function calculateTotalGainItems($collection_items)
    {

        return $collection_items->sum(function ($item) {
            return ($item['unit_price'] - $item['purchase_unit_price']) * $item['quantity'];
        });
    }

    private function calculateCashTotal($methods_payment)
    {
        return collect($methods_payment)
            ->filter(function ($method) {

                return $method['is_cash'];
            })
            ->sum('sum');
    }

    private function calculateCreditTotal($methods_payment)
    {
        return collect($methods_payment)
            ->filter(function ($method) {
                return $method['is_credit'];
            })
            ->sum('sum');
    }

    private function calculateBankTotal($methods_payment)
    {
        return collect($methods_payment)
            ->filter(function ($method) {
                return $method['is_bank'];
            })
            ->sum('sum');
    }
    private function calculateVirtualTotal($methods_payment)
    {
        return collect($methods_payment)
            ->filter(function ($method) {
                return $method['is_digital'];
            })
            ->sum('sum');
    }

    private function calculateFinalBalances(&$data)
    {
        $cash_final_balance = $data['final_balance'] + $data['cash']->beginning_balance;

        $data['cash_beginning_balance'] = self::FormatNumber($data['cash']->beginning_balance);
        $data['cash_final_balance'] = self::FormatNumber($cash_final_balance + $data['cash_egress']);
        $data['cash_income'] = self::FormatNumber($data['cash_income']);
        $data['total_cash_payment_method_type_01'] = self::FormatNumber($this->getTotalCashPaymentMethodType01($data));
        $data['total_efectivo'] =  $data['cash_income'] - $data['cash_egress'];

        return $data;
    }

    public static function getStateTypeId()
    {
        return [
            '01', //Registrado
            '03', // Enviado
            '05', // Aceptado
            '07', // Observado
            '13' // Por anular
        ];
    }
    public static function CalculeTotalOfCurency(
        $total = 0,
        $currency_type_id = 'PEN',
        $exchange_rate_sale = 1
    ) {
        if ($currency_type_id !== 'PEN') {
            $total = $total * $exchange_rate_sale;
        }
        return $total;
    }
    public static function getStringPaymentMethod($payment_id)
    {
        $payment_method = PaymentMethodType::find($payment_id);
        return (!empty($payment_method)) ? $payment_method->description : '';
    }
    public static function FormatNumber($number = 0, $decimal = 2, $decimal_separador = '.', $miles_separador = '')
    {
        return number_format($number, $decimal, $decimal_separador, $miles_separador);
    }

    private function getDocumentProcessor($payment_type)
    {
        return DocumentProcessorFactory::make($payment_type);
    }

    public function getPdf($cash, $format = 'a4', $mm = null, $withBank = false)
    {
        $data = $this->setDataToReport($cash, $withBank);
        $company = Company::first();
        $establishment = Establishment::find(auth()->user()->establishment_id);
        $data['methods_payment'] = array_filter($data['methods_payment'], function ($method) {
            return isset($method['sum']) && $method['sum'] > 0;
        });

        return [
            'status' => true,
            'data' => $data,

        ];
        // 
        //dd($data);

        $data['methods_payment'] = array_values($data['methods_payment']);
        foreach ($data['methods_payment'] as $index => &$method) {
            $method['iteracion'] = $index + 1;
        }
        unset($method);
        $quantity_rows = 30; //$cash->cash_documents()->count();

        $width = 78;
        if ($mm != null) {
            $width = $mm - 2;
        }
        //dd($format);
        $view = view('pos::cash.report_pdf_' . $format, compact('data'));
        if ($format === 'simple_a4') {
            $view = view('pos::cash.report_pdf_' . $format, compact('data'));
        }
        if ($format === 'simple_a4_seller') {
            $view = view('pos::cash.report_pdf_' . $format, compact('data'));
        }
        $html = $view->render();

        $pdf = new Mpdf([
            'mode' => 'utf-8',
        ]);
        if ($format === 'ticket') {
            $pdf = new Mpdf([
                'mode'          => 'utf-8',
                'format'        => [
                    $width,
                    190 +
                        ($quantity_rows * 8),
                ],
                'margin_top'    => 3,
                'margin_right'  => 3,
                'margin_bottom' => 3,
                'margin_left'   => 3,
            ]);
        }

        $pdf->WriteHTML($html);

        return $pdf->output('', 'S');
    }

    private function formatPaymentMethods($methods_payment)
    {
        $temp = [];
        foreach ($methods_payment as $index => $item) {
            $temp[] = [
                'iteracion' => $index + 1,
                'name' => $item->name,
                'sum' => self::FormatNumber($item->sum),
                'is_bank' => $item->is_bank,
                'is_credit' => $item->is_credit,
                'is_cash' => $item->is_cash,
                'is_digital' => $item->is_digital,
                'payment_method_type_id' => $item->id ?? null,
            ];
        }
        return $temp;
    }

    private function getNotes($cash_id, $document_type_id)
    {
        $documents = Document::with(['document_type', 'person', 'payment_method_type'])
            ->where('cash_id', $cash_id)
            ->where('document_type_id', $document_type_id)
            ->where('state_type_id', '05')
            ->get();



        return [
            'documents' => $documents->map(function ($row) {
                return $this->transformSimpleDocument($row);
            }),

        ];
    }
    private function getCreditDocuments($cash_id)
    {
        $documents = Document::with(['document_type', 'person', 'payment_method_type'])
            ->where('cash_id', $cash_id)
            ->whereIn('document_type_id', ['03', '01'])
            ->where('payment_condition_id', '02')
            ->get();

        $sale_notes = SaleNote::with(['person', 'payment_method_type'])
            ->whereHas('payment_method_type', function ($query) {
                $query->where('is_credit', true);
            })
            ->where('cash_id', $cash_id)
            ->get();

        return [
            'documents' => $documents->map(function ($row) {
                return $this->transformSimpleDocument($row);
            }),
            'sale_notes' => $sale_notes->map(function ($row) {
                return $this->transformSimpleDocument($row);
            })
        ];
    }
    private function transformSimpleDocument($document)
    {
        return [
            'number' => $document->number_full,
            'date' => $document->date_of_issue,
            'total' => $document->total,
        ];
    }

    // private function transformSimpleDocument($sale_note){
    //     return [
    //         'number' => $sale_note->number_full ,
    //         'date' => $sale_note->date_of_issue,
    //         'total' => $sale_note->total,
    //     ];
    // }

    private function removeDuplicates($documents)
    {
        return collect($documents)->unique('number')->values()->all();
    }



    private function getCashTransactions($cash_id)
    {
        return GlobalPayment::select([
            'id', 'destination_id', 'destination_type',
            'payment_id', 'payment_type'
        ])
            ->where('destination_id', $cash_id)
            ->where('destination_type', Cash::class)
            ->where('payment_type', 'Modules\Pos\Models\CashTransaction')
            ->get();
    }

    private function processCashTransactions($cash_transactions, &$methods_payment, &$result)
    {
        $processor = new CashTransactionProcessor();

        $cash_transactions->each(function ($cash_transaction) use ($processor, &$methods_payment, &$result) {
            if ($document_data = $processor->process($cash_transaction, [], $methods_payment, $result)) {
                $result['all_documents']->push($document_data);
            }
        });
    }

    /**
     * Obtiene todos los documentos y notas de venta asociados a una caja
     * independientemente de si tienen pagos o no
     * 
     * @param int $cash_id ID de la caja
     * @return array Array con documentos y notas de venta
     */
    private function getAllDocumentsByCash($cash_id)
    {
        // Obtener documentos (facturas, boletas, etc.) asociados a la caja
        $documents = Document::with(['document_type', 'person', 'items', 'payment_method_type'])
            ->where('cash_id', $cash_id)
            ->whereIn('state_type_id', self::getStateTypeId())
            ->where(function ($query) {
                $query->whereNull('quotation_id') // No tiene cotización
                    ->orWhereHas('quotation', function ($q2) {
                        $q2->doesntHave('payments'); // Tiene cotización, pero sin pagos
                    });
            })
            ->get();



        // Obtener notas de venta asociadas a la caja
        $sale_notes = SaleNote::with(['person', 'items', 'payment_method_type'])
            ->where('cash_id', $cash_id)
            ->whereIn('state_type_id', self::getStateTypeId())
            ->where(function ($query) {
                $query->whereNull('quotation_id') // No tiene cotización
                    ->orWhereHas('quotation', function ($q2) {
                        $q2->doesntHave('payments'); // Tiene cotización, pero sin pagos
                    });
            })
            ->get();


        $quotations = Quotation::with(['person', 'items', 'payment_method_type'])
            ->where('cash_id', $cash_id)
            ->whereIn('state_type_id', self::getStateTypeId())
            // ->whereHas('payments', function (Builder $q) use ($cash_id) {
            //     $q->whereHas('global_payment', function (Builder $q2) use ($cash_id) {
            //         $q2->where('payment_type', QuotationPayment::class)
            //             ->where('destination_id', $cash_id)
            //             ->where('destination_type', Cash::class);
            //     });
            // })
            ->get();
        
    

        return [
            'documents' => $documents,
            'sale_notes' => $sale_notes,
            'quotations' => $quotations
        ];
    }

    /**
     * Procesa los documentos obtenidos directamente por cash_id para agregarlos a los resultados
     * 
     * @param array $all_documents Documentos obtenidos por getAllDocumentsByCash
     * @param array $result Array de resultados a actualizar
     * @param array $status_type_id Estados de documento válidos
     * @param bool $withGainItems Si se debe calcular la ganancia de items
     */
    private function processAllDocumentsByCash($all_documents, &$result, $status_type_id, $withGainItems = false, $cash_id = null)
    {
        $sum_comission = 0;
        $processed_document_keys = [];

        // Procesador para documentos
        $document_processor = $this->getDocumentProcessor('App\Models\Tenant\DocumentPayment');



        // Procesador para notas de venta
        $sale_note_processor = $this->getDocumentProcessor('App\Models\Tenant\SaleNotePayment');

        // Procesador para cotizaciones
        $quotation_processor = $this->getDocumentProcessor('Modules\Sale\Models\QuotationPayment');

        // Procesar documentos (facturas, boletas, etc.)
        foreach ($all_documents['documents'] as $document) {
            // Verificar si el documento ya fue procesado (podría haber sido procesado por pagos)
            $document_key = 'doc_' . $document->id;


            if (isset($processed_document_keys[$document_key])) {
                continue;
            }

            $processed_document_keys[$document_key] = true;

            // Obtener el ID del vendedor
            $seller_id = $document->seller_id ?? $document->user_id;

            // Calcular la ganancia del documento
            $document_gain = 0;
            $document_comission = 0;
            if ($withGainItems && $document->items && count($document->items) > 0) {
                // Utilizar el mismo procesador para calcular la ganancia
            
                $total = $document->total;
                $payment_by_cash_id = DocumentPayment::where('document_id', $document->id)->whereHas('global_payment', function($query) use ($cash_id){
                    $query->where('destination_id', $cash_id)
                        ->where('destination_type', Cash::class);
                })->sum('payment');
                if($payment_by_cash_id == 0 && $seller_id == 19){
                    $cash = Cash::select('id','date_opening','date_closed')->find($cash_id);
                    $payment_by_cash_id = DocumentPayment::where('document_id', $document->id)->whereBetween('date_of_payment', [$cash->date_opening, $cash->date_closed])->sum('payment');
                }
                $percentage_payment_by_cash_id = $payment_by_cash_id / $total;
        
                $gain_items = $document_processor->getGainByItems($document->items, $document_key);
                $document_gain = $gain_items['gain'] * $percentage_payment_by_cash_id;
                $document_comission = $gain_items['comission'] * $percentage_payment_by_cash_id;
            }

            // Determinar si es a crédito o contado
            $is_credit = $document->payment_condition_id === '02';

            // Actualizar la colección de vendedores
            // Usamos 0 como payment_amount porque no estamos procesando un pago específico
            // El total del documento ya está en $document->total
            $document_processor->processSeller($result, $seller_id, $document, 0, [], $document->document_type_id, $document_gain);

            // Agregar el documento a la lista de documentos procesados
            $result['all_documents']->push([
                'type_transaction' => 'Venta',
                'document_type_description' => $document->document_type->description,
                'number' => $document->number_full,
                'date_of_issue' => $document->date_of_issue->format('Y-m-d'),
                'date_sort' => $document->date_of_issue,
                'customer_name' => $document->customer->name,
                'customer_number' => $document->customer->number,
                'total' => $document->total,
                'total_seller' => $document->payments->sum('payment'),
                'currency_type_id' => $document->currency_type_id,
                'payment_condition_id' => $document->payment_condition_id ?? '01',
                'payment_condition_type_id' => $document->payment_condition_id ?? '01',
                'total_payments' => $document->total, // Consideramos el total del documento
                'total_gain' => $document_gain,
                'total_comission' => $document_comission,
                'type_transaction_prefix' => 'income',
                'document_type_id' => $document->document_type_id,
                'total_tips' => 0,
                'seller_id' => $seller_id,
                'comission' => $document_comission,
            ]);
        }

        // Procesar notas de venta
        foreach ($all_documents['sale_notes'] as $sale_note) {
            // Verificar si la nota ya fue procesada (podría haber sido procesada por pagos)
            $note_key = 'note_' . $sale_note->id;

            if (isset($processed_document_keys[$note_key])) {
                continue;
            }

            $processed_document_keys[$note_key] = true;

            // Obtener el ID del vendedor
            $seller_id = $sale_note->seller_id ?? $sale_note->user_id;

            // Calcular la ganancia de la nota
            $note_gain = 0;
            $note_comission = 0;
            if ($withGainItems && $sale_note->items && count($sale_note->items) > 0) {
                // Utilizar el mismo procesador para calcular la ganancia
                $total = $sale_note->total;
                $payment_by_cash_id = $sale_note->payments->where('global_payment.destination_id', $cash_id)->sum('payment');
                $percentage_payment_by_cash_id = $payment_by_cash_id / $total;
                $gain_items = $sale_note_processor->getGainByItems($sale_note->items, $note_key);
                $note_gain = $gain_items['gain'] * $percentage_payment_by_cash_id;
                $note_comission = $gain_items['comission'] * $percentage_payment_by_cash_id;
            }
        

            // Determinar si es a crédito o contado
            $is_credit = $sale_note->payment_condition_id === '02';

            // Actualizar la colección de vendedores
            // Usamos 0 como payment_amount porque no estamos procesando un pago específico
            // El total de la nota ya está en $sale_note->total
            $sale_note_processor->processSeller($result, $seller_id, $sale_note, 0, [], 'sale_note', $note_gain);

            // Agregar la nota a la lista de documentos procesados
            $result['all_documents']->push([
                'type_transaction' => 'Venta',
                'document_type_description' => 'NOTA DE VENTA',
                'number' => $sale_note->number_full,
                'date_of_issue' => $sale_note->date_of_issue->format('Y-m-d'),
                'date_sort' => $sale_note->date_of_issue,
                'customer_name' => $sale_note->customer->name,
                'customer_number' => $sale_note->customer->number,
                'payment_condition_id' => $sale_note->payment_condition_id ?? '01',
                'payment_condition_type_id' => $sale_note->payment_condition_id ?? '01',
                'total' => $sale_note->total,
                'total_seller' => $sale_note->payments->sum('payment'),
                'currency_type_id' => $sale_note->currency_type_id,
                'total_payments' => $sale_note->total, // Consideramos el total de la nota
                'total_gain' => $note_gain,
                'total_comission' => $note_comission,
                'type_transaction_prefix' => 'income',
                'seller_id' => $seller_id,
                'comission' => $note_comission,
            ]);
        }

        // Procesar cotizaciones
        foreach ($all_documents['quotations'] as $quotation) {
            // Verificar si la cotización ya fue procesada (podría haber sido procesada por pagos)
            $quotation_key = 'quotation_' . $quotation->id;

            if (isset($processed_document_keys[$quotation_key])) {
                continue;
            }

            $processed_document_keys[$quotation_key] = true;

            // Obtener el ID del vendedor
            $seller_id = $quotation->seller_id ?? $quotation->user_id;

            // Calcular la ganancia de la cotización
            $quotation_gain = 0;
            $quotation_comission = 0;
            if ($withGainItems && $quotation->items && count($quotation->items) > 0) {
                // Utilizar el mismo procesador para calcular la ganancia
                $total = $quotation->total;
                $payment_by_cash_id = $quotation->payments->where('global_payment.destination_id', $cash_id)->sum('payment');
                $percentage_payment_by_cash_id = $payment_by_cash_id / $total;
                $gain_items = $quotation_processor->getGainByItems($quotation->items, $quotation_key);
                $quotation_gain = $gain_items['gain'] * $percentage_payment_by_cash_id;
                $quotation_comission = $gain_items['comission'] * $percentage_payment_by_cash_id;
            }

            // Determinar si es a crédito o contado
            $is_credit = $quotation->payment_condition_id === '02';

            // Actualizar la colección de vendedores
            // Usamos 0 como payment_amount porque no estamos procesando un pago específico
            // El total de la cotización ya está en $quotation->total
            $quotation_processor->processSeller($result, $seller_id, $quotation, 0, [], 'quotation', $quotation_gain);
            // Agregar la cotización a la lista de documentos procesados
            $result['all_documents']->push([
                'type_transaction' => 'Venta',
                'document_type_description' => 'COTIZACIÓN',
                'number' => $quotation->number_full,
                'date_of_issue' => $quotation->date_of_issue->format('Y-m-d'),
                'date_sort' => $quotation->date_of_issue,
                'customer_name' => $quotation->customer->name,
                'customer_number' => $quotation->customer->number,
                'payment_condition_id' => $quotation->payment_condition_id ?? '01',
                'payment_condition_type_id' => $quotation->payment_condition_id ?? '01',
                'total' => $quotation->total,
                'total_seller' => $quotation->payments->sum('payment'),
                'currency_type_id' => $quotation->currency_type_id,
                'total_payments' => $quotation->total, // Consideramos el total de la cotización
                'total_gain' => $quotation_gain,
                'total_comission' => $quotation_comission,
                'type_transaction_prefix' => 'income',
                'document_type_id' => $quotation->document_type_id,
                'total_tips' => 0,
                'seller_id' => $seller_id,
                'comission' => $quotation_comission,
            ]);
        }
    }
}

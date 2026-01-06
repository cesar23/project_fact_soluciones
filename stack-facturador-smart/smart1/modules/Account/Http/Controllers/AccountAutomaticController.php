<?php

namespace Modules\Account\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\System\SubDiary;
use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\Document;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Account\Http\Resources\System\SubdiaryCollection;
use Modules\Account\Models\AccountAutomatic;
use Modules\Account\Models\AccountMonth;
use Modules\Account\Models\AccountSubDiary;

class AccountAutomaticController extends Controller
{
    protected $manualCorrelatives = [];
    protected $document_types = [];
    public $descriptions_accounts = [];
    protected $document_types_ids = [
        '01',
        '03',
        '07',
        '08',
    ];
    protected $sale_accounts = [
        [
            'code' => '121201',
            'description' => 'FACTURAS POR COBRAR EMITIDAS CARTERA TERCEROS M.N.',
            'is_debit' => true,
            'is_credit' => false
        ],
        [
            'code' => '701211',
            'description' => 'MERCADERIAS VENTA LOCAL TERCEROS',
            'is_debit' => false,
            'is_credit' => true
        ],
        [
            'code' => '401111',
            'description' => 'IGV - CUENTA PROPIA',
            'is_debit' => false,
            'is_credit' => true
        ]
    ];
    protected $sale_payment_account = [
        [
            'code' => '101101',
            'description' => 'CAJA M.N.',
            'is_debit' => true,
            'is_credit' => false
        ],
        [
            'code' => '121201',
            'description' => 'FACTURAS POR COBRAR EMITIDAS CARTERA TERCEROS M.N.',
            'is_debit' => false,
            'is_credit' => true
        ]
    ];
    protected $sale_return_account = [
        [
            'code' => '709221',
            'description' => 'MERCADERIAS - VENTA LOCAL  RELACIONADAS',
            'is_debit' => true,
            'is_credit' => false
        ],
        [
            'code' => '401111',
            'description' => 'IGV - CUENTA PROPIA',
            'is_debit' => true,
            'is_credit' => false
        ],
        [
            'code' => '121201',
            'description' => 'FACTURAS POR COBRAR EMITIDAS CARTERA TERCEROS M.N.',
            'is_debit' => false,
            'is_credit' => true
        ]
    ];
    protected $purchase_accounts = [
        [
            'code' => '601101',
            'description' => 'MERCADERIAS',
            'is_debit' => true,
            'is_credit' => false
        ],
        [
            'code' => '401111',
            'description' => 'IGV - CUENTA PROPIA',
            'is_debit' => true,
            'is_credit' => false
        ],
        [
            'code' => '421201',
            'description' => 'FACTURAS EMITIDAS POR PAGAR M.N. TERCEROS',
            'is_debit' => false,
            'is_credit' => true
        ]
    ];

    protected $purchase_payment_account = [
        [
            'code' => '421201',
            'description' => 'FACTURAS EMITIDAS POR PAGAR M.N. TERCEROS',
            'is_debit' => true,
            'is_credit' => false
        ],
        [
            'code' => '101101',
            'description' => 'CAJA M.N.',
            'is_debit' => false,
            'is_credit' => true
        ]
    ];

    protected $purchase_destiny_accounts = [
        [
            'code' => '201111',
            'description' => 'MERCADERIAS - COSTO',
            'is_debit' => true,
            'is_credit' => false
        ],
        [
            'code' => '601101',
            'description' => 'MERCADERIAS',
            'is_debit' => false,
            'is_credit' => true
        ]
    ];

    protected $purchase_return_account = [
        [
            'code' => '421201',
            'description' => 'FACTURAS EMITIDAS POR PAGAR M.N. TERCEROS',
            'is_debit' => true,
            'is_credit' => false
        ],
        [
            'code' => '401111',
            'description' => 'IGV - CUENTA PROPIA',
            'is_debit' => false,
            'is_credit' => true
        ],
        [
            'code' => '601101',
            'description' => 'MERCADERIAS',
            'is_debit' => false,
            'is_credit' => true
        ],
    
    ];




    public function index()
    {
        $system_sub_diaries = new SubdiaryCollection(SubDiary::all());
        return view('account::sub_diaries.create_automatic', compact('system_sub_diaries'));
    }

    public function records(Request $request)
    {
        $records = AccountAutomatic::all();

        $formatted_records = $records->map(function ($record) {
            return [
                'id' => $record->id,
                'description' => $record->description,
                'type' => $record->type,
                'active' => $record->active,
                'items' => $record->items
            ];
        });

        return [
            'success' => true,
            'data' => $formatted_records
        ];
    }

    public function delete(Request $request)
    {
        $automatic = AccountAutomatic::findOrFail($request->input('id'));
        $automatic->items()->delete();
        $automatic->delete();
        return [
            'success' => true,
            'message' => 'Período eliminado'
        ];
    }

    public function record($id)
    {
        $record = AccountAutomatic::with('items')->findOrFail($id);
        return [
            'id' => $record->id,
            'description' => $record->description,
            'type' => $record->type,
            'active' => $record->active,
            'items' => $record->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'code' => $item->code,
                    'description' => $item->getDescription(),
                    'is_debit' => (bool) $item->is_debit,
                    'is_credit' => (bool) $item->is_credit,
                    'info' => $item->info
                ];
            })
        ];
    }

    public function store(Request $request)
    {
        try {
            DB::connection('tenant')->beginTransaction();

            $id = $request->input('id');
            $type = $request->input('type');

            if (!$id) {
                $existe = AccountAutomatic::where('type', $type)->first();
                if ($existe) {
                    return [
                        'success' => false,
                        'message' => 'Ya existe un registro automático para este tipo'
                    ];
                }
            }
            $automatic = AccountAutomatic::findOrNew($id);
            if (!$id) {
                $request->merge(['active' => true]);
            }
            $automatic->fill($request->all());
            $automatic->save();

            $automatic->items()->delete();
            foreach ($request->input('items') as $item) {
                $automatic->items()->create($item);
            }

            DB::connection('tenant')->commit();

            return [
                'success' => true,
                'message' => $id ? 'Período actualizado' : 'Período creado',
                'id' => $automatic->id
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
            $automatic = AccountAutomatic::findOrFail($id);
            $automatic->delete();

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

    public function disable(Request $request)
    {
        $automatic = AccountAutomatic::findOrFail($request->input('id'));
        $automatic->active = false;
        $automatic->save();
        return [
            'success' => true,
            'message' => 'Período desactivado'
        ];
    }

    public function enable(Request $request)
    {
        $automatic = AccountAutomatic::findOrFail($request->input('id'));
        $automatic->active = true;
        $automatic->save();
        return [
            'success' => true,
            'message' => 'Período activado'
        ];
    }
    private function clearRegisterAcountMonth($account_month_id)
    {
        // Obtener los correlativos existentes de asientos manuales
        $manualCorrelatives = DB::connection('tenant')
            ->table('account_sub_diaries')
            ->where('account_month_id', $account_month_id)
            ->where('is_manual', true)
            ->whereNotNull('correlative_number')
            ->select('code', 'correlative_number')
            ->get()
            ->groupBy('code')
            ->map(function($items) {
                return $items->pluck('correlative_number')->toArray();
            })
            ->toArray();

        // Guardar los correlativos manuales en una propiedad de la clase
        $this->manualCorrelatives = $manualCorrelatives;

        // Eliminar items en lote usando foreign key
        DB::connection('tenant')
            ->table('account_sub_diary_items')
            ->whereIn('account_sub_diary_id', function ($query) use ($account_month_id) {
                $query->select('id')
                    ->from('account_sub_diaries')
                    ->where('account_month_id', $account_month_id)
                    ->where('is_manual', false);
            })
            ->delete();

        // Eliminar subdiarios en lote
        DB::connection('tenant')
            ->table('account_sub_diaries')
            ->where('account_month_id', $account_month_id)
            ->where('is_manual', false)
            ->delete();

        // Actualizar account_month en una sola consulta
        DB::connection('tenant')
            ->table('account_months')
            ->where('id', $account_month_id)
            ->update([
                'balance' => 0,
                'total_debit' => 0,
                'total_credit' => 0,
                'updated_at' => now()
            ]);
    }

    private function getNextCorrelative($code, $month_id, $date)
    {
        // Obtener todos los correlativos existentes para este código y mes
        $existingCorrelatives = DB::connection('tenant')
            ->table('account_sub_diaries')
            ->where('code', $code)
            ->where(function($query) use ($month_id, $date) {
                if ($month_id) {
                    $query->where('account_month_id', $month_id);
                } else {
                    $query->whereMonth('date', $date->month)
                          ->whereYear('date', $date->year);
                }
            })
            ->whereNotNull('correlative_number')
            ->pluck('correlative_number')
            ->toArray();

        // Agregar los correlativos manuales guardados
        if (isset($this->manualCorrelatives[$code])) {
            $existingCorrelatives = array_merge(
                $existingCorrelatives,
                $this->manualCorrelatives[$code]
            );
        }

        // Encontrar el siguiente correlativo disponible
        $correlative = 1;
        while (in_array($correlative, $existingCorrelatives)) {
            $correlative++;
        }

        return $correlative;
    }
    private function getDescriptionsAccounts(){
        $this->descriptions_accounts = collect([]);
        $connection = DB::connection('tenant');
        $accounts = $connection->table('account_automatic_items')->distinct()->pluck('code');
        $accounts = $connection->table('ledger_accounts_tenant')->whereIn('code', $accounts)->get();
        $this->descriptions_accounts = $accounts->pluck('name', 'code');
    }
    /**
     * Método original de procesamiento de documentos (versión no optimizada)
     * Este método se mantiene por compatibilidad pero se recomienda usar processDocumentsOptimized
     */
    public function processDocuments(Request $request)
    {
        $this->document_types = DocumentType::all()->pluck('description', 'id')->toArray();
        $month = $request->input('month');
        $account_month_id = $request->input('account_month_id');
        $this->clearRegisterAcountMonth($account_month_id);
        $this->getDescriptionsAccounts();
        $rangeDate = $this->getRangeDate($month);

        try {
            DB::connection('tenant')->beginTransaction();

            // Procesar ventas (documents)
            $documents = DB::connection('tenant')
                ->table('documents')
                ->select([
                    'id',
                    'series',
                    'number',
                    'date_of_issue',
                    'document_type_id',
                    'currency_type_id',
                    'exchange_rate_sale',
                    'total',
                    'total_igv',
                    'total_value',
                    'payment_condition_id'
                ])
                ->whereBetween('date_of_issue', [$rangeDate['start_date'], $rangeDate['end_date']])
                ->orderBy('date_of_issue')
                ->get();

            foreach ($documents as $document) {
                $result = $this->processDocumentOptimized($document, $account_month_id);
                if (!$result) {
                    throw new \Exception('Error al procesar el documento de venta');
                }
            }

            // Procesar compras (purchases)
            $purchases = DB::connection('tenant')
                ->table('purchases')
                ->select([
                    'id',
                    'series',
                    'number',
                    'date_of_issue',
                    'document_type_id',
                    'currency_type_id',
                    'exchange_rate_sale',
                    'total',
                    'total_igv',
                    'total_taxed',
                    'payment_condition_id'
                ])
                ->whereIn('document_type_id', $this->document_types_ids)
                ->whereBetween('date_of_issue', [$rangeDate['start_date'], $rangeDate['end_date']])
                ->orderBy('date_of_issue')
                ->get();

            foreach ($purchases as $purchase) {
                $result = $this->processPurchaseDocumentOptimized($purchase, $account_month_id);
                if (!$result) {
                    throw new \Exception('Error al procesar el documento de compra');
                }
            }

            $payments_credit = DB::connection('tenant')
                ->table('document_payments')
                ->join('documents', 'document_payments.document_id', '=', 'documents.id')
                ->where('documents.payment_condition_id', '=', '02')
                ->whereBetween('document_payments.date_of_payment', [$rangeDate['start_date'], $rangeDate['end_date']])
                ->select([
                    'document_payments.id',
                    'documents.document_type_id',
                    'documents.exchange_rate_sale',
                    'documents.currency_type_id',
                    'documents.series',
                    'documents.number',
                    'document_payments.date_of_payment',
                    'document_payments.document_id',
                    'document_payments.payment as total',
                    'documents.payment_condition_id'
                ])
                ->get();

            foreach ($payments_credit as $payment) {
                $result = $this->processPaymentCreditDocumentOptimized($payment, $account_month_id);
                if (!$result) {
                    throw new \Exception('Error al procesar el documento de pago a credito');
                }
            }

            // Calcular balance una sola vez al final
            $month = AccountMonth::findOrFail($account_month_id);
            $month->calculateBalance();
            $period = $month->accountPeriod;
            $period->calculateBalance();

            $month->last_syncronitation = now();
            $month->save();

            DB::connection('tenant')->commit();

            return [
                'success' => true,
                'message' => 'Período procesado (ventas y compras)',
            ];
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error($e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'Error al procesar documentos: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Método optimizado de procesamiento de documentos
     * 
     * Optimizaciones implementadas:
     * 1. Procesamiento en lotes (batch processing)
     * 2. Inserción masiva de subdiarios e items
     * 3. Carga única de tipos de documentos
     * 4. Reducción de consultas individuales
     * 5. Mejor manejo de memoria
     * 
     * Beneficios esperados:
     * - 70-80% de reducción en tiempo de ejecución
     * - Menor uso de memoria
     * - Menos conexiones a la base de datos
     * - Mejor escalabilidad
     */
    public function processDocumentsOptimized(Request $request)
    {
        $month = $request->input('month');
        $account_month_id = $request->input('account_month_id');
        $rangeDate = $this->getRangeDate($month);

        // Cargar tipos de documentos una sola vez
        $this->document_types = DocumentType::all()->pluck('description', 'id')->toArray();

        try {
            DB::connection('tenant')->beginTransaction();

            // Limpiar registros existentes
            $this->clearRegisterAcountMonth($account_month_id);

            // Procesar todos los documentos en lotes
            $this->processDocumentsBatch($rangeDate, $account_month_id);
            $this->processPurchasesBatch($rangeDate, $account_month_id);
            $this->processPaymentsBatch($rangeDate, $account_month_id);

            // Calcular balance una sola vez
            $this->updateAccountMonth($account_month_id);

            DB::connection('tenant')->commit();

            return ['success' => true, 'message' => 'Período procesado'];
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }


    private function getDescriptionSale($document)
    {
        $document_type_id = $document->document_type_id;
        $document_type = $this->document_types[$document_type_id];
        $number_full = $document->series . '-' . $document->number;
        $not_is_pen = $document->currency_type_id !== 'PEN';
        if (in_array($document_type_id, DocumentType::SALE_DOCUMENT_TYPES) || $document_type_id == DocumentType::DEBIT_NOTE_ID) {
            $description = "Venta con " . $document_type . " " . $number_full;
        } else {
            $description = "Devolución de " . $document_type . " " . $number_full;
        }
        if ($not_is_pen) {
            $exchange_rate_sale = $document->exchange_rate_sale;
            $description .= " con tasa de cambio " . $exchange_rate_sale;
        }
        return $description;
    }


    private function getCorrelativeNumber($account_month_id)
    {
    }

    private function getRangeDate($monthInitDate)
    {
        $monthInitDate = Carbon::parse($monthInitDate);
        $monthEndtDate = $monthInitDate->copy()->endOfMonth();
        return [
            'start_date' => $monthInitDate->format('Y-m-d'),
            'end_date' => $monthEndtDate->format('Y-m-d')
        ];
    }



    /**
     * Redondea un monto a 2 decimales para evitar problemas de precisión
     */
    private function roundAmount($amount)
    {
        return round($amount, 2);
    }

    /**
     * Verifica y aplica ajustes a subdiarios existentes que no tengan ajuste aplicado
     */
    public function checkAndApplyAdjustments(Request $request)
    {
        try {
            DB::connection('tenant')->beginTransaction();

            $sub_diaries = AccountSubDiary::with('items')->get();
            $adjusted_count = 0;

            /** @var AccountSubDiary $sub_diary */
            foreach ($sub_diaries as $sub_diary) {
                $total_debit = $sub_diary->items()->where('debit', true)->sum('debit_amount');
                $total_credit = $sub_diary->items()->where('credit', true)->sum('credit_amount');
                $difference = abs($total_debit - $total_credit);

                // Si hay diferencia pequeña y no tiene ajuste aplicado
                if ($difference > 0 && $difference <= 0.09 && $sub_diary->amount_adjustment == 0) {
                    $adjustment_amount = $this->roundAmount($difference);

                    if ($total_debit > $total_credit) {
                        // Ajustar el crédito (última cuenta de crédito)
                        $last_credit_item = $sub_diary->items()->where('credit', true)->orderBy('correlative_number', 'desc')->first();
                        if ($last_credit_item) {
                            $last_credit_item->credit_amount = $this->roundAmount($last_credit_item->credit_amount + $adjustment_amount);
                            $last_credit_item->amount_adjustment = $adjustment_amount;
                            $last_credit_item->save();
                        }
                    } else {
                        // Ajustar el debe (primera cuenta)
                        $first_debit_item = $sub_diary->items()->where('debit', true)->orderBy('correlative_number', 'asc')->first();
                        if ($first_debit_item) {
                            $first_debit_item->debit_amount = $this->roundAmount($first_debit_item->debit_amount + $adjustment_amount);
                            $first_debit_item->amount_adjustment = $adjustment_amount;
                            $first_debit_item->save();
                        }
                    }

                    $sub_diary->amount_adjustment = $adjustment_amount;
                    $sub_diary->save();

                    $adjusted_count++;
                }
            }

            DB::connection('tenant')->commit();

            return [
                'success' => true,
                'message' => "Se aplicaron ajustes a $adjusted_count subdiarios",
                'adjusted_count' => $adjusted_count
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
     * Procesa un documento optimizado (sin transacciones individuales)
     */
    private function processDocumentOptimized($document, $account_month_id)
    {
        $total = $document->total;
        $total_igv = $document->total_igv;
        $total_value = $document->total_value;
        $currency_type_id = $document->currency_type_id;
        $exchange_rate_sale = $document->exchange_rate_sale;
        $is_note_credit = $document->document_type_id == DocumentType::CREDIT_NOTE_ID;

        if ($currency_type_id !== 'PEN') {
            $total = $total * $exchange_rate_sale;
            $total_igv = $total_igv * $exchange_rate_sale;
            $total_value = $total_value * $exchange_rate_sale;
        }

        $code = '05';
        $book_code = '';
        $date = $document->date_of_issue;
        $number_full = $document->series . '-' . $document->number;
        $description = $this->getDescriptionSale($document);
        $is_manual = false;
        $payment_condition_id = $document->payment_condition_id;

        // Obtener el siguiente correlativo disponible
        $correlative = $this->getNextCorrelative($code, $account_month_id, Carbon::parse($date));

        // Crear subdiario y obtener el ID real
        $subDiary = AccountSubDiary::create([
            'code' => $code,
            'date' => $date,
            'description' => $description,
            'book_code' => $book_code,
            'complete' => false,
            'is_manual' => $is_manual,
            'account_month_id' => $account_month_id,
            'correlative_number' => $correlative
        ]);

        // Crear items del subdiario usando el ID real
        $type = $is_note_credit ? 'sale_return' : 'sale';
        $this->createSubDiaryItemsOptimized($total, $total_igv, $total_value, $description, $number_full, $subDiary->id, $code, $payment_condition_id, $type);
        if ($payment_condition_id == '01' && !$is_note_credit) {
            $this->createSubDiaryItemsOptimized($total, $total_igv, $total_value, $description, $number_full, $subDiary->id, $code, $payment_condition_id, 'sale_payment');
        }

        return true;
    }

    private function processPaymentCreditDocumentOptimized($payment, $account_month_id)
    {
        $total = $payment->total;
        $currency_type_id = $payment->currency_type_id;
        $exchange_rate_sale = $payment->exchange_rate_sale;

        if ($currency_type_id !== 'PEN') {
            $total = $total * $exchange_rate_sale;
        }

        $code = '05';
        $book_code = '';
        $date = $payment->date_of_payment;
        $number_full = $payment->series . '-' . $payment->number;
        $description = "Cobro de cuota del documento " . $number_full;
        $is_manual = false;

        // Obtener el siguiente correlativo disponible
        $correlative = $this->getNextCorrelative($code, $account_month_id, Carbon::parse($date));

        $subDiary = AccountSubDiary::create([
            'code' => $code,
            'date' => $date,
            'description' => $description,
            'book_code' => $book_code,
            'complete' => false,
            'is_manual' => $is_manual,
            'account_month_id' => $account_month_id,
            'correlative_number' => $correlative
        ]);

        $this->createSubDiaryItemsOptimized($total, 0, 0, $description, $number_full, $subDiary->id, $code, '02', 'sale_payment');

        return true;
    }

    /**
     * Procesa una compra optimizada (sin transacciones individuales)
     */
    private function processPurchaseDocumentOptimized($purchase, $account_month_id)
    {
        $total = $purchase->total;
        $total_igv = $purchase->total_igv;
        $total_taxed = $purchase->total_taxed;
        $currency_type_id = $purchase->currency_type_id;
        $exchange_rate_sale = $purchase->exchange_rate_sale;

        if ($currency_type_id !== 'PEN') {
            $total = $total * $exchange_rate_sale;
            $total_igv = $total_igv * $exchange_rate_sale;
            $total_taxed = $total_taxed * $exchange_rate_sale;
        }

        $code = '11'; // Código para compras
        $book_code = '';
        $date = $purchase->date_of_issue;
        $number_full = $purchase->series . '-' . $purchase->number;
        $description = $this->getDescriptionPurchase($purchase);
        $is_manual = false;
        $payment_condition_id = $purchase->payment_condition_id;

        // Obtener el siguiente correlativo disponible
        $correlative = $this->getNextCorrelative($code, $account_month_id, Carbon::parse($date));

        // Crear subdiario y obtener el ID real
        $subDiary = AccountSubDiary::create([
            'code' => $code,
            'date' => $date,
            'description' => $description,
            'book_code' => $book_code,
            'complete' => false,
            'is_manual' => $is_manual,
            'account_month_id' => $account_month_id,
            'correlative_number' => $correlative
        ]);

        // Crear items del subdiario usando el ID real
        $this->createSubDiaryItemsOptimized($total, $total_igv, $total_taxed, $description, $number_full, $subDiary->id, $code, $payment_condition_id, 'purchase');
        $this->createSubDiaryItemsOptimized($total, $total_igv, $total_taxed, $description, $number_full, $subDiary->id, $code, $payment_condition_id, 'purchase_destiny');

        if ($payment_condition_id == '01') {
            $this->createSubDiaryItemsOptimized($total, $total_igv, $total_taxed, $description, $number_full, $subDiary->id, $code, $payment_condition_id, 'purchase_payment');
        }

        return true;
    }

    /**
     * Genera la descripción para compras
     */
    private function getDescriptionPurchase($purchase)
    {
        $document_type_id = $purchase->document_type_id;
        $document_type = $this->document_types[$document_type_id];
        $number_full = $purchase->series . '-' . $purchase->number;
        $not_is_pen = $purchase->currency_type_id !== 'PEN';

        if (!in_array($document_type_id, ['08', '07'])) {
            $description = "Compra con " . $document_type . " " . $number_full;
        } else {
            $description = "Devolución de compra con " . $document_type . " " . $number_full;
        }

        if ($not_is_pen) {
            $exchange_rate_sale = $purchase->exchange_rate_sale;
            $description .= " con tasa de cambio " . $exchange_rate_sale;
        }

        return $description;
    }

    private function getAutomaticAccounts($type){

        $account_automatic = AccountAutomatic::where('type', $type)->first();
        if(!$account_automatic){
            return [];
        }
        $items = $account_automatic->items;
        if(!$items){
            return [];
        }
        return $items->transform(function ($item)  {
            return [
                'code' => $item->code,
                'description' => $this->descriptions_accounts[$item->code],
                'is_debit' => $item->is_debit,
                'is_credit' => $item->is_credit
            ];
        });
    }

    /**
     * Crea los items del subdiario optimizado (para ventas y compras)
     */
    private function getAccounts($type)
    {
        // Mapeo de tipos a cuentas por defecto
        $defaultAccounts = [
            'sale' => $this->sale_accounts,
            'purchase' => $this->purchase_accounts,
            'purchase_payment' => $this->purchase_payment_account,
            'purchase_destiny' => $this->purchase_destiny_accounts,
            'sale_payment' => $this->sale_payment_account,
            'sale_return' => $this->sale_return_account,
            'purchase_return' => $this->purchase_return_account,
        ];

        // Obtener cuentas automáticas configuradas
        $automaticAccounts = $this->getAutomaticAccounts($type);
        
        // Retornar cuentas automáticas si existen, sino las por defecto
        return $automaticAccounts && count($automaticAccounts) > 0 
            ? $automaticAccounts 
            : ($defaultAccounts[$type] ?? []);
    }
    private function createSubDiaryItemsOptimized($total, $total_igv, $total_value, $description, $number_full, $subDiaryId, $code, $payment_condition_id, $type = 'sale')
    {
        $items = [];
        $total_debit = 0;
        $total_credit = 0;
        $adjustment_amount = 0;

        // Seleccionar las cuentas según el tipo
        $accounts = $this->getAccounts($type);

        // Crear los items del subdiario
        foreach ($accounts as $idx => $account) {
            $total_amount = 0;

            if ($type === 'sale') {
                // Lógica para ventas
                if ($idx == 0) {
                    $total_amount = $this->roundAmount($total);
                    $total_debit += $total_amount;
                } else if ($idx == 1) {
                    $total_amount = $this->roundAmount($total_value);
                    $total_credit += $total_amount;
                } else if ($idx == 2) {
                    $total_amount = $this->roundAmount($total_igv);
                    if ($total_amount == 0) {
                        continue;
                    }
                    $total_credit += $total_amount;
                }
            } else if ($type == 'purchase_payment') {
                if ($idx == 0) {
                    $total_amount = $this->roundAmount($total);
                    $total_debit += $total_amount;
                } else if ($idx == 1) {
                    $total_amount = $this->roundAmount($total);
                    $total_credit += $total_amount;
                }
            } else if ($type == 'purchase_return') {
                if ($idx == 0) {
                    $total_amount = $this->roundAmount($total_value);
                    $total_debit += $total_amount;
                } else if ($idx == 1) {
                    $total_amount = $this->roundAmount($total_igv);
                    $total_debit += $total_amount;
                } else if ($idx == 2) {
                    $total_amount = $this->roundAmount($total);
                    $total_credit += $total_amount;
                }
            } else if ($type == 'sale_return') {
                if ($idx == 0) {
                    $total_amount = $this->roundAmount($total_value);
                    $total_debit += $total_amount;
                } else if ($idx == 1) {
                    $total_amount = $this->roundAmount($total_igv);
                    $total_debit += $total_amount;
                } else if ($idx == 2) {
                    $total_amount = $this->roundAmount($total);
                    $total_credit += $total_amount;
                }
            } else if ($type == 'sale_payment') {
                if ($idx == 0) {
                    $total_amount = $this->roundAmount($total);
                    $total_debit += $total_amount;
                } else if ($idx == 1) {
                    $total_amount = $this->roundAmount($total);
                    $total_credit += $total_amount;
                }
            } else if ($type == 'purchase_destiny') {
                if ($idx == 0) {
                    $total_amount = $this->roundAmount($total_value);
                    $total_debit += $total_amount;
                } else if ($idx == 1) {
                    $total_amount = $this->roundAmount($total_value);
                    $total_credit += $total_amount;
                }
            } else if ($type == 'purchase') {
                // Lógica para compras
                if ($idx == 0) {
                    // Valor total de las mercaderías compradas (débito)
                    $total_amount = $this->roundAmount($total_value); // total_taxed para compras
                    $total_debit += $total_amount;
                } else if ($idx == 1) {
                    // Valor total del IGV (débito)
                    $total_amount = $this->roundAmount($total_igv);
                    if ($total_amount == 0) {
                        continue;
                    }
                    $total_debit += $total_amount;
                } else if ($idx == 2) {
                    // Total a pagar por la compra (crédito)
                    $total_amount = $this->roundAmount($total);
                    $total_credit += $total_amount;
                }
            }

            $correlative = $idx + 1;
            $items[] = [
                'code' => $account['code'],
                'correlative_number' => $correlative,
                'description' => $account['description'],
                'general_description' => $description,
                'document_number' => $number_full,
                'debit' => $account['is_debit'],
                'credit' => $account['is_credit'],
                'debit_amount' => $account['is_debit'] ? $total_amount : 0,
                'credit_amount' => $account['is_credit'] ? $total_amount : 0,
                'amount_adjustment' => 0,
                'account_sub_diary_id' => $subDiaryId,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // Calcular ajustes si es necesario
        $difference = abs($total_debit - $total_credit);
        if ($difference > 0 && $difference <= 0.09) {
            $adjustment_amount = $this->roundAmount($difference);

            if ($total_debit > $total_credit) {
                // Ajustar crédito (última cuenta de crédito)
                $last_credit_index = -1;
                for ($i = count($items) - 1; $i >= 0; $i--) {
                    if ($items[$i]['credit']) {
                        $last_credit_index = $i;
                        break;
                    }
                }
                if ($last_credit_index >= 0) {
                    $items[$last_credit_index]['credit_amount'] = $this->roundAmount($items[$last_credit_index]['credit_amount'] + $adjustment_amount);
                    $items[$last_credit_index]['amount_adjustment'] = $adjustment_amount;
                }
            } else {
                // Ajustar débito (primera cuenta de débito)
                $first_debit_index = -1;
                for ($i = 0; $i < count($items); $i++) {
                    if ($items[$i]['debit']) {
                        $first_debit_index = $i;
                        break;
                    }
                }
                if ($first_debit_index >= 0) {
                    $items[$first_debit_index]['debit_amount'] = $this->roundAmount($items[$first_debit_index]['debit_amount'] + $adjustment_amount);
                    $items[$first_debit_index]['amount_adjustment'] = $adjustment_amount;
                }
            }
        }

        // Insertar todos los items de una vez
        if (!empty($items)) {
            DB::connection('tenant')->table('account_sub_diary_items')->insert($items);
        }
    }



    private function calculateAmounts($document)
    {
        $total = $document->total;
        $total_igv = $document->total_igv;
        $total_value = $document->total_value;

        if ($document->currency_type_id !== 'PEN') {
            $exchange_rate = $document->exchange_rate_sale;
            $total *= $exchange_rate;
            $total_igv *= $exchange_rate;
            $total_value *= $exchange_rate;
        }

        return [
            'total' => $this->roundAmount($total),
            'total_igv' => $this->roundAmount($total_igv),
            'total_value' => $this->roundAmount($total_value)
        ];
    }

    private function processDocumentsBatch($rangeDate, $account_month_id)
    {
        // Obtener todos los documentos de una vez
        $documents = DB::connection('tenant')
            ->table('documents')
            ->select([
                'id',
                'series',
                'number',
                'date_of_issue',
                'document_type_id',
                'currency_type_id',
                'exchange_rate_sale',
                'total',
                'total_igv',
                'total_value',
                'payment_condition_id'
            ])
            ->whereBetween('date_of_issue', [$rangeDate['start_date'], $rangeDate['end_date']])
            ->orderBy('date_of_issue')
            ->get();

        if ($documents->isEmpty()) {
            return;
        }

        // Preparar datos para inserción masiva
        $subDiaries = [];
        $subDiaryItems = [];

        foreach ($documents as $document) {
            $amounts = $this->calculateAmounts($document);
            $description = $this->getDescriptionSale($document);
            $number_full = $document->series . '-' . $document->number;

            // Crear subdiario
            $subDiaryData = [
                'code' => '05',
                'date' => $document->date_of_issue,
                'description' => $description,
                'book_code' => '',
                'complete' => false,
                'is_manual' => false,
                'account_month_id' => $account_month_id,
                'created_at' => now(),
                'updated_at' => now()
            ];
            $subDiaries[] = $subDiaryData;
        }

        // Inserción masiva de subdiarios
        if (!empty($subDiaries)) {
            DB::connection('tenant')->table('account_sub_diaries')->insert($subDiaries);

            // Obtener los IDs de los subdiarios insertados
            $insertedSubDiaries = DB::connection('tenant')
                ->table('account_sub_diaries')
                ->where('account_month_id', $account_month_id)
                ->where('code', '05')
                ->where('is_manual', false)
                ->orderBy('id')
                ->get(['id', 'description', 'date']);

            // Crear items para cada subdiario
            foreach ($documents as $index => $document) {
                $subDiary = $insertedSubDiaries[$index];
                $amounts = $this->calculateAmounts($document);
                $description = $this->getDescriptionSale($document);
                $number_full = $document->series . '-' . $document->number;

                // Crear items para venta
                $saleItems = $this->prepareSubDiaryItems($amounts, $description, $number_full, $subDiary->id, '05', $document->payment_condition_id, 'sale');
                $subDiaryItems = array_merge($subDiaryItems, $saleItems);

                // Si es pago contado, crear items de pago
                if ($document->payment_condition_id == '01') {
                    $paymentItems = $this->prepareSubDiaryItems($amounts, $description, $number_full, $subDiary->id, '05', $document->payment_condition_id, 'sale_payment');
                    $subDiaryItems = array_merge($subDiaryItems, $paymentItems);
                }
            }
        }

        // Inserción masiva de items
        if (!empty($subDiaryItems)) {
            DB::connection('tenant')->table('account_sub_diary_items')->insert($subDiaryItems);
        }
    }

    private function processPurchasesBatch($rangeDate, $account_month_id)
    {
        // Obtener todas las compras de una vez
        $purchases = DB::connection('tenant')
            ->table('purchases')
            ->select([
                'id',
                'series',
                'number',
                'date_of_issue',
                'document_type_id',
                'currency_type_id',
                'exchange_rate_sale',
                'total',
                'total_igv',
                'total_taxed',
                'payment_condition_id'
            ])
            ->whereIn('document_type_id', $this->document_types_ids)
            ->whereBetween('date_of_issue', [$rangeDate['start_date'], $rangeDate['end_date']])
            ->orderBy('date_of_issue')
            ->get();

        if ($purchases->isEmpty()) {
            return;
        }

        // Preparar datos para inserción masiva
        $subDiaries = [];
        $subDiaryItems = [];

        foreach ($purchases as $purchase) {
            $amounts = $this->calculatePurchaseAmounts($purchase);
            $description = $this->getDescriptionPurchase($purchase);
            $number_full = $purchase->series . '-' . $purchase->number;

            // Crear subdiario
            $subDiaryData = [
                'code' => '6',
                'date' => $purchase->date_of_issue,
                'description' => $description,
                'book_code' => '',
                'complete' => false,
                'is_manual' => false,
                'account_month_id' => $account_month_id,
                'created_at' => now(),
                'updated_at' => now()
            ];
            $subDiaries[] = $subDiaryData;
        }

        // Inserción masiva de subdiarios
        if (!empty($subDiaries)) {
            DB::connection('tenant')->table('account_sub_diaries')->insert($subDiaries);

            // Obtener los IDs de los subdiarios insertados
            $insertedSubDiaries = DB::connection('tenant')
                ->table('account_sub_diaries')
                ->where('account_month_id', $account_month_id)
                ->where('code', '06')
                ->where('is_manual', false)
                ->orderBy('id')
                ->get(['id', 'description', 'date']);

            // Crear items para cada subdiario
            foreach ($purchases as $index => $purchase) {
                $subDiary = $insertedSubDiaries[$index];
                $amounts = $this->calculatePurchaseAmounts($purchase);
                $description = $this->getDescriptionPurchase($purchase);
                $number_full = $purchase->series . '-' . $purchase->number;
                $is_note_credit = $purchase->document_type_id == DocumentType::CREDIT_NOTE_ID;
                $type = $is_note_credit ? 'purchase_return' : 'purchase';

                // Crear items para compra
                $purchaseItems = $this->prepareSubDiaryItems($amounts, $description, $number_full, $subDiary->id, '06', $purchase->payment_condition_id, $type);
                $subDiaryItems = array_merge($subDiaryItems, $purchaseItems);

                if (!$is_note_credit) {
                    // Crear items para destino de compra
                    $destinyItems = $this->prepareSubDiaryItems($amounts, $description, $number_full, $subDiary->id, '06', $purchase->payment_condition_id, 'purchase_destiny');
                    $subDiaryItems = array_merge($subDiaryItems, $destinyItems);

                    // Si es pago contado, crear items de pago
                    if ($purchase->payment_condition_id == '01') {
                        $paymentItems = $this->prepareSubDiaryItems($amounts, $description, $number_full, $subDiary->id, '06', $purchase->payment_condition_id, 'purchase_payment');
                        $subDiaryItems = array_merge($subDiaryItems, $paymentItems);
                    }
                }
            }
        }

        // Inserción masiva de items
        if (!empty($subDiaryItems)) {
            DB::connection('tenant')->table('account_sub_diary_items')->insert($subDiaryItems);
        }
    }

    private function processPaymentsBatch($rangeDate, $account_month_id)
    {
        // Obtener todos los pagos de crédito de una vez
        $payments_credit = DB::connection('tenant')
            ->table('document_payments')
            ->join('documents', 'document_payments.document_id', '=', 'documents.id')
            ->where('documents.payment_condition_id', '=', '02')
            ->whereBetween('document_payments.date_of_payment', [$rangeDate['start_date'], $rangeDate['end_date']])
            ->select([
                'document_payments.id',
                'documents.document_type_id',
                'documents.exchange_rate_sale',
                'documents.currency_type_id',
                'documents.series',
                'documents.number',
                'document_payments.date_of_payment',
                'document_payments.document_id',
                'document_payments.payment as total',
                'documents.payment_condition_id'
            ])
            ->get();

        if ($payments_credit->isEmpty()) {
            return;
        }

        // Preparar datos para inserción masiva
        $subDiaries = [];
        $subDiaryItems = [];

        foreach ($payments_credit as $payment) {
            $amounts = $this->calculatePaymentAmounts($payment);
            $number_full = $payment->series . '-' . $payment->number;
            $description = "Cobro de cuota del documento " . $number_full;

            // Crear subdiario
            $subDiaryData = [
                'code' => '05',
                'date' => $payment->date_of_payment,
                'description' => $description,
                'book_code' => '',
                'complete' => false,
                'is_manual' => false,
                'account_month_id' => $account_month_id,
                'created_at' => now(),
                'updated_at' => now()
            ];
            $subDiaries[] = $subDiaryData;
        }

        // Inserción masiva de subdiarios
        if (!empty($subDiaries)) {
            DB::connection('tenant')->table('account_sub_diaries')->insert($subDiaries);

            // Obtener los IDs de los subdiarios insertados
            $insertedSubDiaries = DB::connection('tenant')
                ->table('account_sub_diaries')
                ->where('account_month_id', $account_month_id)
                ->where('code', '05')
                ->where('is_manual', false)
                ->orderBy('id')
                ->get(['id', 'description', 'date']);

            // Crear items para cada subdiario
            foreach ($payments_credit as $index => $payment) {
                $subDiary = $insertedSubDiaries[$index];
                $amounts = $this->calculatePaymentAmounts($payment);
                $number_full = $payment->series . '-' . $payment->number;
                $description = "Cobro de cuota del documento " . $number_full;

                // Crear items de pago
                $paymentItems = $this->prepareSubDiaryItems($amounts, $description, $number_full, $subDiary->id, '05', '02', 'sale_payment');
                $subDiaryItems = array_merge($subDiaryItems, $paymentItems);
            }
        }

        // Inserción masiva de items
        if (!empty($subDiaryItems)) {
            DB::connection('tenant')->table('account_sub_diary_items')->insert($subDiaryItems);
        }
    }

    private function calculatePurchaseAmounts($purchase)
    {
        $total = $purchase->total;
        $total_igv = $purchase->total_igv;
        $total_taxed = $purchase->total_taxed;

        if ($purchase->currency_type_id !== 'PEN') {
            $exchange_rate = $purchase->exchange_rate_sale;
            $total *= $exchange_rate;
            $total_igv *= $exchange_rate;
            $total_taxed *= $exchange_rate;
        }

        return [
            'total' => $this->roundAmount($total),
            'total_igv' => $this->roundAmount($total_igv),
            'total_value' => $this->roundAmount($total_taxed)
        ];
    }

    private function calculatePaymentAmounts($payment)
    {
        $total = $payment->total;

        if ($payment->currency_type_id !== 'PEN') {
            $exchange_rate = $payment->exchange_rate_sale;
            $total *= $exchange_rate;
        }

        return [
            'total' => $this->roundAmount($total),
            'total_igv' => 0,
            'total_value' => 0
        ];
    }

    private function prepareSubDiaryItems($amounts, $description, $number_full, $subDiaryId, $code, $payment_condition_id, $type = 'sale')
    {
        $items = [];
        $total_debit = 0;
        $total_credit = 0;

        // Seleccionar las cuentas según el tipo
        $accounts = $this->getAccounts($type);

        // Crear los items del subdiario
        foreach ($accounts as $idx => $account) {
            $total_amount = 0;

            if ($type === 'sale') {
                if ($idx == 0) {
                    $total_amount = $amounts['total'];
                    $total_debit += $total_amount;
                } else if ($idx == 1) {
                    $total_amount = $amounts['total_value'];
                    $total_credit += $total_amount;
                } else if ($idx == 2) {
                    $total_amount = $amounts['total_igv'];
                    if ($total_amount == 0) {
                        continue;
                    }
                    $total_credit += $total_amount;
                }
            } else if ($type == 'purchase_payment') {
                if ($idx == 0) {
                    $total_amount = $amounts['total'];
                    $total_debit += $total_amount;
                } else if ($idx == 1) {
                    $total_amount = $amounts['total'];
                    $total_credit += $total_amount;
                }
            } else if ($type == 'sale_payment') {
                if ($idx == 0) {
                    $total_amount = $amounts['total'];
                    $total_debit += $total_amount;
                } else if ($idx == 1) {
                    $total_amount = $amounts['total'];
                    $total_credit += $total_amount;
                }
            } else if ($type == 'purchase_destiny') {
                if ($idx == 0) {
                    $total_amount = $amounts['total_value'];
                    $total_debit += $total_amount;
                } else if ($idx == 1) {
                    $total_amount = $amounts['total_value'];
                    $total_credit += $total_amount;
                }
            } else if ($type == 'purchase') {
                if ($idx == 0) {
                    $total_amount = $amounts['total_value'];
                    $total_debit += $total_amount;
                } else if ($idx == 1) {
                    $total_amount = $amounts['total_igv'];
                    if ($total_amount == 0) {
                        continue;
                    }
                    $total_debit += $total_amount;
                } else if ($idx == 2) {
                    $total_amount = $amounts['total'];
                    $total_credit += $total_amount;
                }
            }

            $correlative = $idx + 1;
            $items[] = [
                'code' => $account['code'],
                'correlative_number' => $correlative,
                'description' => $account['description'],
                'general_description' => $description,
                'document_number' => $number_full,
                'debit' => $account['is_debit'],
                'credit' => $account['is_credit'],
                'debit_amount' => $account['is_debit'] ? $total_amount : 0,
                'credit_amount' => $account['is_credit'] ? $total_amount : 0,
                'amount_adjustment' => 0,
                'account_sub_diary_id' => $subDiaryId,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // Aplicar ajustes si es necesario
        $this->applyAdjustments($items, $total_debit, $total_credit);

        return $items;
    }

    private function applyAdjustments(&$items, $total_debit, $total_credit)
    {
        $difference = abs($total_debit - $total_credit);
        if ($difference > 0 && $difference <= 0.09) {
            $adjustment_amount = $this->roundAmount($difference);

            if ($total_debit > $total_credit) {
                // Ajustar crédito (última cuenta de crédito)
                $last_credit_index = -1;
                for ($i = count($items) - 1; $i >= 0; $i--) {
                    if ($items[$i]['credit']) {
                        $last_credit_index = $i;
                        break;
                    }
                }
                if ($last_credit_index >= 0) {
                    $items[$last_credit_index]['credit_amount'] = $this->roundAmount($items[$last_credit_index]['credit_amount'] + $adjustment_amount);
                    $items[$last_credit_index]['amount_adjustment'] = $adjustment_amount;
                }
            } else {
                // Ajustar débito (primera cuenta de débito)
                $first_debit_index = -1;
                for ($i = 0; $i < count($items); $i++) {
                    if ($items[$i]['debit']) {
                        $first_debit_index = $i;
                        break;
                    }
                }
                if ($first_debit_index >= 0) {
                    $items[$first_debit_index]['debit_amount'] = $this->roundAmount($items[$first_debit_index]['debit_amount'] + $adjustment_amount);
                    $items[$first_debit_index]['amount_adjustment'] = $adjustment_amount;
                }
            }
        }
    }

    private function updateAccountMonth($account_month_id)
    {
        $month = AccountMonth::findOrFail($account_month_id);
        $month->calculateBalance();
        $period = $month->accountPeriod;
        $period->calculateBalance();
        $month->last_syncronitation = now();
        $month->save();
    }
}

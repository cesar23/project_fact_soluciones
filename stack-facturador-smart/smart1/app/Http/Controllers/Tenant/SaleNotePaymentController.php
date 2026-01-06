<?php

namespace App\Http\Controllers\Tenant;

use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\CoreFacturalo\Template;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\SaleNotePaymentRequest;
use App\Http\Resources\Tenant\SaleNotePaymentCollection;
use App\Models\Tenant\Company;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\SaleNotePayment;
use FontLib\Table\Type\loca;
use Illuminate\Support\Facades\Log;
use Mpdf\Mpdf;
use Mpdf\HTMLParserMode;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Modules\Finance\Traits\FinanceTrait;
use Modules\Finance\Traits\FilePaymentTrait;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant\CashDocumentCredit;
use App\Models\Tenant\Cash;
use App\Models\Tenant\DispatchOrderPayment;
use App\Models\Tenant\Document;
use App\Models\Tenant\ProductionOrder;
use App\Models\Tenant\ProductionOrderPayment;
use App\Models\Tenant\Quotation;
use App\Models\Tenant\User;
use Exception;
use Illuminate\Http\Request;
use Modules\BusinessTurn\Models\BusinessTurn;
use Modules\Sale\Models\QuotationPayment;

class SaleNotePaymentController extends Controller
{
    use StorageDocument, FinanceTrait, FilePaymentTrait;

    public function records($sale_note_id)
    {
        $hasQuotation = false;
        $quotationNumber = null;
        $sale_note = SaleNote::select('id','quotation_id','total')->where('id', $sale_note_id)->first();
        if($sale_note->quotation_id){
            $quotation_id = $sale_note->quotation_id;
            $q_payments = QuotationPayment::where('quotation_id', $quotation_id)->sum('payment');
            if($q_payments >= $sale_note->total){
                $hasQuotation = true;
                $quotation = Quotation::select('number','prefix')->where('id', $quotation_id)->first();
                $quotationNumber = $quotation->prefix.'-'.$quotation->number;
            }
        }
        $records = SaleNotePayment::where('sale_note_id', $sale_note_id)->get();
        request()->merge(['hasQuotation' => $hasQuotation, 'quotationNumber' => $quotationNumber]);
        return new SaleNotePaymentCollection($records);
    }

    public function recordsQuotation($quotation_id)
    {
        $records = SaleNotePayment::where('quotation_id', $quotation_id)->get();
        return new SaleNotePaymentCollection($records);
    }

    public function tables()
    {
        return [
            'payment_method_types' => PaymentMethodType::all(),
            'payment_destinations' => $this->getPaymentDestinations()
        ];
    }

    public function documentSuscription($customer_id)
    {
        $connection = DB::connection('tenant');

        // Opción 1: Usar subconsulta para evitar duplicados
        $result = $connection->table('sale_notes')
            ->select(
                DB::raw('SUM(sale_notes.total) as total_facturado'),
                DB::raw('SUM(COALESCE(payment_totals.total_paid, 0)) as total_paid'),
                DB::raw('SUM(sale_notes.total - COALESCE(payment_totals.total_paid, 0)) as total_pending')
            )
            ->leftJoin(
                DB::raw('(SELECT sale_note_id, SUM(payment) as total_paid FROM sale_note_payments GROUP BY sale_note_id) as payment_totals'),
                'sale_notes.id',
                '=',
                'payment_totals.sale_note_id'
            )
            ->where('customer_id', $customer_id)
            ->where('user_rel_suscription_plan_id', '>', 0)
            ->first();

        // Opción 2: Más simple, hacer dos consultas separadas
        $total_facturado = $connection->table('sale_notes')
            ->where('customer_id', $customer_id)
            ->where('user_rel_suscription_plan_id', '>', 0)
            ->sum('total');

        $total_pagado = $connection->table('sale_note_payments')
            ->join('sale_notes', 'sale_note_payments.sale_note_id', '=', 'sale_notes.id')
            ->where('sale_notes.customer_id', $customer_id)
            ->where('sale_notes.user_rel_suscription_plan_id', '>', 0)
            ->sum('sale_note_payments.payment');

        $total_pending = [
            (object)[
                'total_paid' => $total_pagado,
                'total_pending' => $total_facturado - $total_pagado
            ]
        ];

        return [
            'total_pending' => $total_pending
        ];
    }

    public function document($sale_note_id)
    {
        /** @var SaleNote $sale_note */
        $sale_note = SaleNote::find($sale_note_id);

        $total_paid = round(collect($sale_note->payments)->sum('payment'), 2);
        $total = $sale_note->total;
        $total_difference = round($total - $total_paid, 2);

        if ($total_difference < 0.01) {
            $sale_note->total_canceled = true;
            $sale_note->save();
        }

        return [
            'identifier' => $sale_note->identifier,
            'full_number' => $sale_note->getNumberFullAttribute(),
            'number_full' => $sale_note->getNumberFullAttribute(),
            'total_paid' => $total_paid,
            'total' => $total,
            'total_difference' => $total_difference,
            'paid' => $sale_note->total_canceled,
            'external_id' => $sale_note->external_id,
        ];
    }
    public function getDetailPrePayment($sale_note_payment_id)
    {
        $sale_note_payment = SaleNotePayment::find($sale_note_payment_id);
        $sale_note = SaleNote::find($sale_note_payment->sale_note_id);

        return [
            'success' => true,
            'sale_note' => $sale_note,
        ];
    }

    public function getPrepayment($sale_note_id)
    {
        $documents_ids = SaleNotePayment::where('sale_note_id', $sale_note_id)->pluck('document_prepayment_id')->toArray();

        $documents = Document::whereIn('id', $documents_ids)->get()->transform(function ($row) {
            $total = round($row->pending_amount_prepayment, 2);
            $amount = ($row->affectation_type_prepayment == '10') ? round($total / 1.18, 2) : $total;
            return [
                'document_id' => $row->id,
                'number' => $row->series . '-' . $row->number,
                'document_type_id' => ($row->document_type_id == '01') ? '02' : '03',
                'amount' => $amount,
                'total' => $total,

            ];
        });

        return [
            'success' => true,
            'documents' => $documents,
        ];
    }
    public function store(SaleNotePaymentRequest $request)
    {
        $id = $request->input('id');
        $data = DB::connection('tenant')->transaction(function () use ($id, $request) {

            $record = SaleNotePayment::firstOrNew(['id' => $id]);
            $record->fill($request->all());
            $record->payment = str_replace(',', '', $request->payment);
            $record->cash_id = User::getCashId();
            $record->save();
            $record->sale_note->auditPaymentAdded($record->payment, $record->payment_method_type->description);
            $this->createGlobalPayment($record, $request->all());
            $this->saveFiles($record, $request, 'sale_notes');
            if(!$record->hasGlobalPayment()){
                $user_id = $record->sale_note->user_id;
                $user_tmp = User::find($user_id);
                if($user_tmp->user_cash_id){
                    $user_id = $user_tmp->user_cash_id;
                }
                $custom_row = [
                    'user_id' => $user_id,
                    'payment_destination_id' => 'cash'
                ];
                $this->createGlobalPayment($record, $custom_row);
            }
    
            return $record;
        });

        if ($request->paid == true) {
            $sale_note = SaleNote::find($request->sale_note_id);
            $sale_note->total_canceled = true;
            $sale_note->save();

            $credit = CashDocumentCredit::where([
                ['status', 'PENDING'],
                ['sale_note_id',  $sale_note->id]
            ])->first();

            if ($credit) {

                $cash = Cash::where([
                    ['user_id', User::getUserCashId()],
                    ['state', true],
                ])->first();

                $credit->status = 'PROCESSED';
                $credit->cash_id_processed = $cash->id;
                $credit->save();

                $req = [
                    'document_id' => null,
                    'sale_note_id' => $sale_note->id
                ];

                $cash->cash_documents()->updateOrCreate($req);
            }
        }

    
        $this->createPdf($request->input('sale_note_id'));

        return [
            'success' => true,
            'message' => ($id) ? 'Pago editado con éxito' : 'Pago registrado con éxito',
            'id' => $data->id,
        ];
    }
    public function storeFullSuscriptionPayment(SaleNotePaymentRequest $request)
    {
        $id = $request->input('id');
        $sale_note = SaleNote::find($request->sale_note_id);
        
        if (!$sale_note) {
            return [
                'success' => false,
                'message' => 'Nota de venta no encontrada'
            ];
        }
        
        $data = DB::connection('tenant')->transaction(function () use ($id, $request, $sale_note) {
            $user_rel_suscription_plan_id = $sale_note->user_rel_suscription_plan_id;
            $customer_id = $sale_note->customer_id;
            $payment_amount = (float) str_replace(',', '', $request->payment);
            $remaining_payment = $payment_amount;
            
            // Obtener todas las notas de venta del mismo cliente y plan
            $sale_notes = DB::connection('tenant')->table('sale_notes')
                ->where('customer_id', $customer_id)
                ->where('user_rel_suscription_plan_id', $user_rel_suscription_plan_id)
                ->where('state_type_id', '01') // Solo notas válidas
                ->orderBy('date_of_issue', 'asc') // Procesar por orden de fecha
                ->get();
            
            $processed_notes = [];
            
            foreach ($sale_notes as $note) {
                // Calcular saldo por cobrar de esta nota
                $total_payments = DB::connection('tenant')->table('sale_note_payments')
                    ->where('sale_note_id', $note->id)
                    ->sum('payment');
                
                $balance_due = $note->total - $total_payments;
                
                // Si no hay saldo por cobrar, continuar
                if ($balance_due <= 0) {
                    continue;
                }
                
                // Calcular cuánto pagar de esta nota
                $payment_for_this_note = min($remaining_payment, $balance_due);
                
                // Crear el pago para esta nota
                $record = new SaleNotePayment();
                $record->sale_note_id = $note->id;
                $record->payment = $payment_for_this_note;
                $record->payment_method_type_id = $request->payment_method_type_id;
                $record->date_of_payment = $request->date_of_payment ?? now();
                $record->cash_id = User::getCashId();
                $record->save();
                
                // Crear el pago global
                $this->createGlobalPayment($record, $request->all());
                
                // Guardar archivos si es necesario
                $this->saveFiles($record, $request, 'sale_notes');
                
                // Si no tiene pago global, crear uno
                if (!$record->hasGlobalPayment()) {
                    $user_id = $note->user_id;
                    $user_tmp = User::find($user_id);
                    if($user_tmp->user_cash_id){
                        $user_id = $user_tmp->user_cash_id;
                    }
                    $custom_row = [
                        'user_id' => $user_id,
                        'payment_destination_id' => 'cash'
                    ];
                    $this->createGlobalPayment($record, $custom_row);
                }
                
                $processed_notes[] = [
                    'note_id' => $note->id,
                    'payment' => $payment_for_this_note,
                    'balance_due' => $balance_due
                ];
                
                // Reducir el monto restante
                $remaining_payment -= $payment_for_this_note;
                
                // Si ya no hay monto restante, salir del bucle
                if ($remaining_payment <= 0) {
                    break;
                }
            }
            
            // Si hay monto sobrante, crear o actualizar crédito
            if ($remaining_payment > 0) {
                $this->handleRemainingCredit($customer_id, $remaining_payment);
            }
            
            return [
                'processed_notes' => $processed_notes,
                'remaining_amount' => $remaining_payment,
                'total_payment' => $payment_amount
            ];
        });
        
        // Marcar como pagada si se especifica
        if ($request->paid == true) {
            $sale_note->total_canceled = true;
            $sale_note->save();
        }
        
        return [
            'success' => true,
            'message' => ($id) ? 'Pago editado con éxito' : 'Pago registrado con éxito',
            'data' => $data,
        ];
    }

    public function discount(Request $request)
    {
        try{
            DB::connection('tenant')->beginTransaction();
            $reason = $request->reason;
        $amount = $request->amount;
        $document_id = $request->document_id;
        $sale_note = SaleNote::find($document_id);
        if($sale_note->total_discount > 0){
            return [
                'success' => false,
                'message' => 'No se puede aplicar un descuento adicional, ya que ya se ha aplicado uno',
            ];
        }
        $total = $sale_note->total;
        $new_total = $total - $amount;
        $sale_note->total = $new_total;
        $factor = number_format($amount / $total, 5);
        $discount_type =["discount_type_id"=>"03","description"=>"Descuentos globales que no afectan la base imponible del IGV\/IVAP","factor"=>$factor,"amount"=>$amount,"base"=>$total,"is_amount"=>true];
        $sale_note->total_discount = $amount;
        $discounts = $sale_note->discounts;
        $discounts[] = $discount_type;
        $sale_note->discounts = $discounts;
        $sale_note->additional_information = $reason;
        $sale_note->save();
        DB::connection('tenant')->commit();
        return [
            'success' => true,
            'message' => 'Descuento aplicado con éxito',
        ];
        }catch(Exception $e){
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => 'Error al aplicar el descuento',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Maneja el crédito sobrante
     */
    private function handleRemainingCredit($customer_id, $remaining_amount)
    {
        // Buscar si ya existe un registro de crédito
        $credit_record = DB::connection('tenant')->table('person_full_suscription_credit')
            ->where('person_id', $customer_id)
            ->first();
        
        if ($credit_record) {
            // Actualizar el monto existente
            DB::connection('tenant')->table('person_full_suscription_credit')
                ->where('person_id', $customer_id)
                ->update([
                    'amount' => DB::raw("amount + {$remaining_amount}"),
                    'updated_at' => now()
                ]);
        } else {
            // Crear nuevo registro de crédito
            DB::connection('tenant')->table('person_full_suscription_credit')->insert([
                'person_id' => $customer_id,
                'amount' => $remaining_amount,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
    function deletePaymentIntegrateSystem($sale_note_id, $sale_note_payment)
    {

        $amount = $sale_note_payment->payment;
        $date_of_payment = $sale_note_payment->date_of_payment;
        $payment_method_type_id = $sale_note_payment->payment_method_type_id;

        $production_order_payment = ProductionOrderPayment::whereHas('production_order', function ($query) use ($sale_note_id) {
            $query->where('sale_note_id', $sale_note_id);
        })->where('payment', $amount)->where('date_of_payment', $date_of_payment)->where('payment_method_type_id', $payment_method_type_id)->first();

        if ($production_order_payment) {
            $production_order_payment->delete();
        }

        $dispatch_order_payment = DispatchOrderPayment::whereHas('dispatch_order', function ($query) use ($sale_note_id) {
            $query->where('sale_note_id', $sale_note_id);
        })->where('payment', $amount)->where('date_of_payment', $date_of_payment)->where('payment_method_type_id', $payment_method_type_id)->first();

        if ($dispatch_order_payment) {
            $dispatch_order_payment->delete();
        }
    }
    public function destroy($id)
    {
        try {

            DB::connection('tenant')->beginTransaction();
            $is_integrate_system = BusinessTurn::isIntegrateSystem();

            $item = SaleNotePayment::findOrFail($id);
            $sale_note_id = $item->sale_note_id;

            if ($is_integrate_system) {
                $this->deletePaymentIntegrateSystem($sale_note_id, $item);
            }
            $item->delete();

            $sale_note = SaleNote::find($item->sale_note_id);
            $sale_note->total_canceled = false;
            $sale_note->save();

            $this->createPdf($sale_note_id);
            DB::connection('tenant')->commit();
            return [
                'success' => true,
                'message' => 'Pago eliminado con éxito'
            ];
        } catch (Exception $e) {
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => 'No se puede eliminar el pago, ya que se encuentra enlazado con otros registros'
            ];
        }
    }

    public function createPdf($sale_note_id, $format = null)
    {
        $sale_note = SaleNote::find($sale_note_id);
        $total_paid = round(collect($sale_note->payments)->sum('payment'), 2);
        $total = $sale_note->total;
        $total_difference = round($total - $total_paid, 2);

        if ($total_difference == 0) {
            Log::info('true ' . $total_difference);
            $sale_note->total_canceled = true;
        } else {
            Log::info('false ' . $total_difference);
            $sale_note->total_canceled = false;
        }
        $sale_note->save();


        $company = Company::first();

        $template = new Template();
        $pdf = null;
        if ($format == 'a5') {
            $pdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => [
                    78,
                    220
                ],
                'margin_top' => 2,
                'margin_right' => 5,
                'margin_bottom' => 0,
                'margin_left' => 5
            ]);
        } else {
            $pdf = new Mpdf();
        }

        $document = SaleNote::find($sale_note_id);

        $base_template = config('tenant.pdf_template');

        $html = $template->pdf($base_template, "sale_note", $company, $document, "a4");

        $pdf_font_regular = config('tenant.pdf_name_regular');
        $pdf_font_bold = config('tenant.pdf_name_bold');

        if ($pdf_font_regular != false) {
            $defaultConfig = (new ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];

            $defaultFontConfig = (new FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];

            $pdf = new Mpdf([
                'fontDir' => array_merge($fontDirs, [
                    app_path('CoreFacturalo' . DIRECTORY_SEPARATOR . 'Templates' .
                        DIRECTORY_SEPARATOR . 'pdf' .
                        DIRECTORY_SEPARATOR . $base_template .
                        DIRECTORY_SEPARATOR . 'font')
                ]),
                'fontdata' => $fontData + [
                    'custom_bold' => [
                        'R' => $pdf_font_bold . '.ttf',
                    ],
                    'custom_regular' => [
                        'R' => $pdf_font_regular . '.ttf',
                    ],
                ]
            ]);
        }

        $path_css = app_path('CoreFacturalo' . DIRECTORY_SEPARATOR . 'Templates' .
            DIRECTORY_SEPARATOR . 'pdf' .
            DIRECTORY_SEPARATOR . $base_template .
            DIRECTORY_SEPARATOR . 'style.css');

        $stylesheet = file_get_contents($path_css);

        $pdf->WriteHTML($stylesheet, HTMLParserMode::HEADER_CSS);
        $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);

        if (config('tenant.pdf_template_footer')) {
            $html_footer = $template->pdfFooter($base_template, $document);
            $pdf->SetHTMLFooter($html_footer);
        }

        $this->uploadStorage($document->filename, $pdf->output('', 'S'), 'sale_note');
        return $document->filename;
        //        $this->uploadFile($pdf->output('', 'S'), 'sale_note');
    }

    public function toPrint($sale_note_id, $format)
    {
        $filename = $this->createPdf($sale_note_id, $format);
        $temp = tempnam(sys_get_temp_dir(), 'sale_note');
        file_put_contents($temp, $this->getStorage($filename, 'sale_note'));

        /*
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"'
        ];
        */

        return response()->file($temp, $this->generalPdfResponseFileHeaders($filename));
    }

    public function record($sale_note_payment_id)
    {
        $sale_note_payment = SaleNotePayment::find($sale_note_payment_id);
        return [
            'success' => true,
            'payment' => $sale_note_payment
        ];
    }

    public function updateRecord(Request $request, $sale_note_payment_id)
    {
        $sale_note_payment = SaleNotePayment::find($sale_note_payment_id);
        $sale_note_payment->fill($request->all());
        $sale_note_payment->save();
        $sale_note = SaleNote::find($sale_note_payment->sale_note_id);
        $total_payments = $sale_note->payments->sum('payment');
        if($total_payments == $sale_note->total){
            $sale_note->total_canceled = true;
        }else{
            $sale_note->total_canceled = false;
        }
        $sale_note->save();
        return [
            'success' => true,
            'message' => 'Pago actualizado con éxito'
        ];
    }
}
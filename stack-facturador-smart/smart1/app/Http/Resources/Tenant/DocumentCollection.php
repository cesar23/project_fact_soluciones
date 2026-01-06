<?php

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Dispatch;
use App\Models\Tenant\DocumentPayment;
use App\Models\Tenant\EmailSendLog;
use App\Models\Tenant\Invoice;
use App\Models\Tenant\Item;
use App\Models\Tenant\Note;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\SaleNotePayment;
use App\Models\Tenant\User;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;
use Modules\Item\Models\WebPlatform;
use Modules\Order\Models\OrderNote;

class DocumentCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        $establishment = DB::connection('tenant')
            ->table('establishments')
            ->select('print_format')
            ->first();
        $format_default = $establishment->print_format != null ? $establishment->print_format : "";


        $user = auth()->user();

        $states_valid = ['01', '03', '05'];
        $documents_valid = ['01', '03', '08'];
        $can_change_delivery_state = $user->edit_delivery_state || in_array($user->type, ['admin', 'superadmin']);
        $configurations = Configuration::getConfig();
        // Obtener todos los website_ids únicos
        $websiteIds = $this->collection->pluck('website_id')->filter()->unique();
        // Obtener todos los IDs de documentos
        $documentIds = $this->collection->pluck('id');
        $noteGroup = DB::connection('tenant')
            ->table('notes')
            ->leftJoin('documents as d2', 'notes.document_id', '=', 'd2.id')
            ->leftJoin('documents as d', 'notes.affected_document_id', '=', 'd.id')
            ->leftJoin('sale_notes as sn', 'notes.affected_sale_note_id', '=', 'sn.id')
            ->select('d.total', 'notes.id', 'notes.affected_document_id', 'sn.total as total_sale_note', 'd2.total as total_document')
            ->where(function ($query) use ($documentIds) {
                $query->whereIn('affected_document_id', $documentIds)
                    ->orWhereIn('affected_sale_note_id', $documentIds);
            })
            ->get();




        $prepayments = DB::connection('tenant')
            ->table('sale_note_payments')
            ->selectRaw('document_prepayment_id, SUM(payment) as total_payment')
            ->whereIn('document_prepayment_id', $documentIds)
            ->groupBy('document_prepayment_id')
            ->pluck('total_payment', 'document_prepayment_id');
        $paymentsGroup = [];
        // $paymentsGroup = DocumentPayment::whereIn('document_id', $documentIds)
        //     ->selectRaw('document_id, SUM(payment) as total_payment')
        //     ->groupBy('document_id')
        //     ->pluck('total_payment', 'document_id');
        // Batch query para EmailSendLog
        $emailLogs = EmailSendLog::Document()
            ->whereIn('relation_id', $documentIds)
            ->get()
            ->groupBy('relation_id');
        $dateOfDueGroup = [];
        // $dateOfDueGroup = Invoice::whereIn('document_id', $documentIds)
        //     ->selectRaw('document_id, MAX(date_of_due) as date_of_due')
        //     ->groupBy('document_id')
        //     ->pluck('date_of_due', 'document_id');
        // Batch query para SaleNotes (optimización de getNvCollection)
        $saleNotesData = collect();
        $saleNoteIds = $this->collection->pluck('sale_note_id')->filter()->unique();
        if ($saleNoteIds->isNotEmpty()) {
            $saleNotesData = DB::connection('tenant')
                ->table('sale_notes')
                ->join('state_types', 'sale_notes.state_type_id', '=', 'state_types.id')

                ->select(
                    'sale_notes.id',
                    'sale_notes.document_id',
                    'sale_notes.series',
                    'sale_notes.number',
                    'sale_notes.state_type_id',
                    'state_types.description as state_type_description'
                )
                ->whereIn('sale_notes.document_id', $documentIds)
                ->orWhereIn('sale_notes.id', $saleNoteIds)
                ->get()
                ->groupBy('id');
        }

        // Batch query para OrderNotes (optimización de getOrderNoteCollection)
        $orderNotesData = collect();
        $orderNoteIds = $this->collection->pluck('order_note_id')->filter()->unique();
        if ($orderNoteIds->isNotEmpty()) {
            $orderNotesData = OrderNote::whereIn('id', $orderNoteIds)
                ->get()
                ->keyBy('id');
        }

        // Batch query para companies
        $companies = collect();
        if ($websiteIds->isNotEmpty()) {
            $companies = Company::whereIn('website_id', $websiteIds)
                ->get()
                ->keyBy('website_id');
        }
        $defaultCompany = Company::active();
        $allItemIds = collect();
        foreach ($this->collection as $row) {
            if ($row->relationLoaded('items')) {
                $allItemIds = $allItemIds->merge($row->items->pluck('item_id'));
            }
        }
        $allItemIds = $allItemIds->unique();

        // Batch query para platforms
        $platformData = collect();
        if ($configurations->show_web_platform_document_sale_note && $allItemIds->isNotEmpty()) {
            $itemsWithPlatforms = DB::connection('tenant')
                ->table('items')
                ->leftJoin('web_platforms', 'items.web_platform_id', '=', 'web_platforms.id')
                ->select(
                    'items.id',
                    'items.name',
                    'items.internal_id',
                    'web_platforms.id as web_platform_id',
                    'web_platforms.name as web_platform_name'
                    // Agrega otras columnas que necesites de items y web_platforms
                )
                ->whereIn('items.id', $allItemIds)
                ->get()
                ->keyBy('id');

            $platformData = $itemsWithPlatforms->pluck('web_platform_name', 'id')
                ->filter();
            // ->unique()
            // ->implode(' / ');
        }
        return $this->collection->transform(function ($row, $key) use ($configurations, $states_valid, $documents_valid, $can_change_delivery_state, $user, $companies, $defaultCompany, $emailLogs, $prepayments, $paymentsGroup, $platformData, $saleNotesData, $orderNotesData, $dateOfDueGroup, $noteGroup, $format_default) {

            $format_default_print = url('/') . "/print/document/{$row->external_id}/a4";
            $has_xml = true;
            $btn_order_delivery = true;
            $has_pdf = true;
            $has_cdr = false;
            $btn_note = false;
            $btn_guide = true; // Boton para generar guia
            $btn_resend = false;
            $btn_voided = false;
            $btn_pdf_voided = false;
            $btn_consult_cdr = false;
            $btn_delete_doc_type_03 = false;
            $btn_constancy_detraction = false;
            $can_be_bill_of_exchange = false;
            $can_be_order_concrete = false;
            $is_nv_note = strpos($row->series, 'N', 0) !== false && $row->document_type_id == '07';


            if (in_array($row->state_type_id, $states_valid) && in_array($row->document_type_id, $documents_valid) && $row->total > 0 && $row->total_canceled == 0) {
                $can_be_bill_of_exchange = true;
            }
            if (in_array($row->state_type_id, $states_valid) && in_array($row->document_type_id, $documents_valid)) {
                $can_be_order_concrete = true;
            }

            $credit_days = "-";
            $affected_document = null;
            $total = $row->total;
            if ($row->perception && $row->perception->amount > 0) {
                $total = $row->total + $row->perception->amount;
            }


            if ($row->group_id === '01') {
                if ($row->state_type_id === '01') {
                    $btn_resend = true;
                }

                if ($row->state_type_id === '05') {
                    $has_cdr = true;
                    $btn_note = true;
                    $btn_resend = false;
                    $btn_voided = true;
                    $btn_consult_cdr = true;
                }
                if ($row->state_type_id === '11') {
                    $btn_pdf_voided = true;
                }

                if (in_array($row->document_type_id, ['07', '08'])) {
                    $btn_note = false;
                }
            }

            if ($row->group_id === '02') {
                if ($row->state_type_id === '05') {
                    $btn_note = true;
                    $btn_voided = true;

                    // envio individual
                    if ($row->isSingleDocumentShipment()) $has_cdr = true;
                    // envio individual

                }

                // envio individual reenviar
                if ($row->state_type_id === '01' && $row->isSingleDocumentShipment()) {
                    $btn_resend = true;
                }
                // envio individual reenviar


                if (in_array($row->document_type_id, ['07', '08'])) {
                    $btn_note = false;
                }

                if ($row->document_type_id === '03' && config('tenant.delete_document_type_03')) {

                    if ($row->state_type_id === '01' && $row->doesntHave('summary_document')) {
                        $btn_delete_doc_type_03 = true;
                    }
                }
            }
            $btn_guide = $btn_note;
            if ($btn_guide === false && ($row->state_type_id === '01')) {
                // #750
                $btn_guide = true;
            }

            if (in_array($row->document_type_id, ['01', '03'])) {
                $btn_constancy_detraction = ($row->detraction) ? true : false;
            }

            // $btn_recreate_document = config('tenant.recreate_document');
            // $btn_recreate_document = auth()->user()->recreate_documents;
            $btn_recreate_document = $user->type == 'superadmin';

            $btn_change_to_registered_status = false;
            if ($row->state_type_id === '01') {
                $btn_change_to_registered_status = config('tenant.change_to_registered_status');
            }

            // $total_payment = $paymentsGroup->get($row->id, 0);
            $total_payment = $row->payments->sum('payment');
            $not2 = $noteGroup->where('affected_document_id', $row->id)->first();
            if ($not2) {
                $total_payment += $not2->total_document;
            }

            if ($row->bill_of_exchange_id) {
                // $total_payment += $row->bill_of_exchange_document->total;
            }
            $has_auditor_history = $row->auditor_history->count() > 0;
            $total_payment_prepayment = $prepayments->get($row->id, 0);

            $total_payment += $total_payment_prepayment;

            $balance = number_format($row->total - $total_payment, 2, ".", "");

            if ($row->sale_note_id) {
                $sale_note = $row->sale_note;
                if ($sale_note->payments && ($sale_note->paid == 1 || $sale_note->total_canceled == 1)) {
                    $total_payment += $sale_note->payments->sum('payment');
                }
            }
            if ($row->retention) {
                $balance = number_format($row->total - $row->retention->amount - $total_payment, 2, ".", "");
            } else {
                $balance = number_format($total - $total_payment, 2, ".", "");
            }
            if ($balance < 0) {
                $balance = number_format(0, 2, ".", "");
            }
            if ($row->document_type_id == "07") {
                $balance = "0.00";
            }
            $message_regularize_shipping = null;

            if ($row->regularize_shipping && isset($row->response_regularize_shipping->code)) {
                $description = $row->response_regularize_shipping->description;
                $code = $row->response_regularize_shipping->code;
                if (strpos($description, "looks like we got no XML document") !== false) {
                    $code = "0";
                    $description = "El documento está en proceso de envío";
                }
                $message_regularize_shipping = "Por regularizar: {$code} - {$description}";
            }

            // Optimización: usar datos batch en lugar de llamar getNvCollection()
            $nvs = collect();
            $relatedSaleNotes = $saleNotesData->get($row->sale_note_id, collect());
            if ($relatedSaleNotes->isNotEmpty()) {
                $nvs = $relatedSaleNotes->transform(function ($sale_note) {
                    if (is_array($sale_note)) {
                        return $sale_note;
                    }
                    return [
                        'id' => $sale_note->id,
                        'number' => $sale_note->series . '-' . $sale_note->number,
                        'state_type_description' => $sale_note->state_type_description,
                    ];
                });
            }

            // Optimización: usar datos batch en lugar de llamar getOrderNoteCollection()
            $order_note = [];
            if ($row->order_note_id && $orderNotesData->has($row->order_note_id)) {
                $orderNote = $orderNotesData->get($row->order_note_id);
                $order_note = $orderNote->getCollectionData();
            }
            // Regresa si se hn enviado correos
            // Reemplazar la consulta N+1 de EmailSendLog
            $email_send_it = false;
            $email_send_it_array = [];
            $send_it = $emailLogs->get($row->id, collect());

            if ($send_it->isNotEmpty()) {
                foreach ($send_it as $log) {
                    $email_send_it_array[] = [
                        'email' => $log->email,
                        'send_it' => $log->sendit,
                        'send_date' => $log->created_at->format('Y-m-d H:i'),
                    ];
                    if ($email_send_it == false) {
                        $email_send_it = $log->sendit;
                    }
                }
            }
            $date_pay = $row->payments;
            $payment = '';
            if (count($date_pay) > 0) {
                foreach ($date_pay as $pay) {
                    $payment = $pay->date_of_payment->format('Y-m-d');
                }
            }

            $btn_retention = !is_null($row->retention);
            if ($row->website_id && $configurations->multi_companies) {
                $company = $companies->get($row->website_id);
            } else {
                $company = $defaultCompany;
            }
            $btn_send_pse = false;
            $btn_check_voided_pse = false;
            $btn_check_pse = false;
            $btn_voided_pse = false;
            if ($row->state_type_id === '03'  && $row->soap_type_id === '02' && $company->pse && $company->type_send_pse == 2) {
                // if($row->state_type_id === '03'  && $company->pse){
                $btn_check_pse = true;
            }

            if ($row->state_type_id === '01'  && $row->soap_type_id === '02' && $company->pse && $company->type_send_pse == 2) {
                // if($row->state_type_id === '03'  && $company->pse){
                $btn_send_pse = true;
            }
            if ($row->state_type_id === '05'  && $row->soap_type_id === '02' && $company->pse && $company->type_send_pse == 2) {
                // if($row->state_type_id === '03'  && $company->pse){
                $btn_voided_pse = true;
                $btn_voided = false;
            }

            if ($row->state_type_id == '13' && $row->soap_type_id === '02' && $company->pse && $company->type_send_pse == 2) {
                $btn_check_voided_pse = true;
            }
            $pending_to_delivery = 3;
            if ($user->create_order_delivery == false) {
                $btn_order_delivery = false;
            }
            if ($row->no_stock) {
                $pending_to_delivery = 2;
                $no_stock_document = $row->no_stock_document;
                if ($no_stock_document) {

                    if ($no_stock_document->completed) {
                        $pending_to_delivery = 1;
                    }
                }
            }
            if ($row->no_stock) {
                $no_stock_document = $row->no_stock_document;
                if ($no_stock_document && $no_stock_document->completed) {
                    $btn_guide = false;
                    $btn_order_delivery = false;
                }
            }
            $alter_company = [];
            $company = $defaultCompany;
            if ($row->website_id) {
                $company = $companies->get($row->website_id);
            }
            $alter_company['name'] = $company->name;
            $alter_company['number'] = $company->number;
            if ($row->fee) {
                $last_fee = $row->fee->last();
                if ($last_fee) {
                    $credit_days = $last_fee->date->format('Y-m-d');
                }
            }
            $plante_numbers = [];
            if ($configurations->plate_number_config) {
                $plante_numbers_description = optional($row->plateNumberDocument)->plateNumber ? optional($row->plateNumberDocument)->plateNumber->description : null;

                $plante_numbers = $plante_numbers_description ? [[
                    'description' => $plante_numbers_description
                ]] : [];
            } else {
                $plante_numbers = $row->getPlateNumbers();
            }

            $platforms = "";
            if ($configurations->show_web_platform_document_sale_note) {
                if ($row->relationLoaded('items')) {
                    $itemIds = $row->items->pluck('item_id')->unique();
                    $platforms = $platformData->only($itemIds)->implode(' / ');
                }
            }
            if ($user->note_cpe == false) {
                $btn_note = false;
            }
            if ($user->create_order_delivery == false) {
                $btn_order_delivery = false;
            }
            if ($user->voided_cpe == false) {
                $btn_voided = false;
                $btn_voided_pse = false;
            }

            $date_of_due = null;
            if (($row->document_type_id === '01' || $row->document_type_id === '03') && $row->invoice != null) {
                // $date_of_due = $dateOfDueGroup->get($row->id, null);
                $date_of_due = $row->invoice->date_of_due;
                if ($date_of_due) {
                    $date_of_due = $date_of_due->format('Y-m-d');
                }
            }
            $show_packers_in_document = $user->show_packers_in_document;
            $show_dispatchers_in_document = $user->show_dispatchers_in_document;
            $show_box_in_document = $user->show_box_in_document;
            return [
                'format_default_print' => $format_default_print,
                'is_nv_note' => $is_nv_note,
                'btn_order_delivery' => $btn_order_delivery,
                'updated' => [
                    'date' => $row->updated_at->format('Y-m-d'),
                    'time' => $row->updated_at->format('H:i:s'),
                ],
                'created' => [
                    'date' => $row->created_at->format('Y-m-d'),
                    'time' => $row->created_at->format('H:i:s'),
                ],
                'reference_data' => $row->reference_data,
                'platforms' => $platforms,
                'can_change_delivery_state' => $can_change_delivery_state,
                'state_delivery_id' => $row->state_delivery_id ?? 1,
                'can_be_order_concrete' => $can_be_order_concrete,
                'can_be_bill_of_exchange' => $can_be_bill_of_exchange,
                'state_validate' => $row->state_validate,
                'date_validate' => $row->date_validate,
                'credit_days' => $credit_days,
                'website_id' => $row->website_id,
                //para que se vea en la listay los valores de estados columnas saldrán del Roe stay validate
                'box' => $row->box,
                'has_auditor_history' => $has_auditor_history,
                'dispatcher_id' => $row->dispatcher_id,
                'customer_id' => $row->customer_id,
                'pending_to_delivery' => $pending_to_delivery,
                'person_packer_id' => $row->person_packer_id,
                'person_dispatcher_id' => $row->person_dispatcher_id,
                'sent_it_email' => $email_send_it,
                'alter_company' => $alter_company,
                'btn_pdf_voided' => $btn_pdf_voided,
                'bill_of_exchange_id' => $row->bill_of_exchange_id,
                'btn_check_voided_pse' => $btn_check_voided_pse,
                'btn_voided_pse' => $btn_voided_pse,
                'btn_send_pse' => $btn_send_pse,
                'btn_check_pse' => $btn_check_pse,
                'appendix_2' => (bool) $row->appendix_2,
                'appendix_3' => (bool) $row->appendix_3,
                'appendix_4' => (bool) $row->appendix_4,
                'appendix_5' => (bool) $row->appendix_5,
                'id' => $row->id,
                'group_id' => $row->group_id,
                'soap_type_id' => $row->soap_type_id,
                'soap_type_description' => $row->soap_type->description,
                'date_of_issue' => $row->date_of_issue->format('Y-m-d'),
                'time_of_issue' => $row->time_of_issue,
                'date_of_due' => $date_of_due,
                'number' => $row->number_full,
                'customer_name' => $row->customer->name,
                'customer_number' => $row->customer->number,
                'customer_trade_name' => $row->customer->trade_name,
                'customer_telephone' => $row->customer->telephone,
                'customer_email' => optional($row->customer)->email,
                'currency_type_id' => $row->currency_type_id,
                'exchange_rate_sale' => $row->exchange_rate_sale,
                'total_exportation' => $row->total_exportation,
                'total_free' => $row->total_free,
                'total_unaffected' => $row->total_unaffected,
                'total_exonerated' => $row->total_exonerated,
                'total_taxed' => $row->total_taxed,
                'total_igv' => $row->total_igv,
                'total' => $total,
                'state_type_id' => $row->state_type_id,
                'state_type_description' => $row->state_type->description,
                'document_type_description' => $row->document_type->description,
                'document_type_id' => $row->document_type->id,
                'has_xml' => $has_xml,
                'has_pdf' => $has_pdf,
                'has_cdr' => $has_cdr,
                'download_xml' => $row->download_external_xml,
                'download_pdf' => $row->download_external_pdf,
                'download_cdr' => $row->download_external_cdr,
                'btn_voided' => $btn_voided,
                'btn_note' => $btn_note,
                'btn_guide' => $btn_guide,
                //                'btn_ticket' => $btn_ticket,
                'btn_resend' => $btn_resend,
                'btn_consult_cdr' => $btn_consult_cdr,
                'btn_constancy_detraction' => $btn_constancy_detraction,
                'btn_recreate_document' => $btn_recreate_document,
                'btn_change_to_registered_status' => $btn_change_to_registered_status,
                'btn_delete_doc_type_03' => $btn_delete_doc_type_03,
                'send_server' => (bool) $row->send_server,
                //                'voided' => $voided,
                'affected_document' => $affected_document,
                //                'has_xml_voided' => $has_xml_voided,
                //                'has_cdr_voided' => $has_cdr_voided,
                //                'download_xml_voided' => $download_xml_voided,
                //                'download_cdr_voided' => $download_cdr_voided,
                'shipping_status' => json_decode($row->shipping_status),
                'sunat_shipping_status' => json_decode($row->sunat_shipping_status),
                'query_status' => json_decode($row->query_status),
                'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $row->updated_at->format('Y-m-d H:i:s'),
                'user_name' => ($row->user) ? $row->user->name : '',
                'user_email' => ($row->user) ? $row->user->email : '',
                'user_id' => $row->user_id,
                'email_send_it' => $email_send_it,
                'email_send_it_array' => $email_send_it_array,
                'external_id' => $row->external_id,
                'ticket_single_shipment' => (bool) $row->ticket_single_shipment,
                'force_send_by_summary' => (bool) $row->force_send_by_summary,

                'notes' => (in_array($row->document_type_id, ['01', '03'])) ? $row->affected_documents->transform(function ($row) {
                    return [
                        'id' => $row->id,
                        'document_id' => $row->document_id,
                        'note_type_description' => ($row->note_type == 'credit') ? 'NC' : 'ND',
                        'description' => $row->document->series . '-' . $row->document->number,
                    ];
                }) : null,
                'affected_documents' => (in_array($row->document_type_id, ['07', '08'])) ? $row->affected_documents2->transform(function ($row) {
                    return [
                        'document_type_id' => isset($row->affected_document) ?  $row->affected_document->document_type_id
                            : (isset($row->data_affected_document) ?
                                $row->data_affected_document->document_type_id
                                : null
                            )
                    ];
                }) : [],
                'auditor_state' => (bool)$row->auditor_state,
                'sales_note' => $nvs,
                'order_note' => $order_note,
                'balance' => $balance,
                'guides' => !empty($row->guides) ? (array)$row->guides : null,
                'message_regularize_shipping' => $message_regularize_shipping,
                'regularize_shipping' => (bool) $row->regularize_shipping,
                'purchase_order' => $row->purchase_order,
                'is_editable' => $row->is_editable,
                'dispatches' => $this->getDispatches($row),
                'soap_type' => $row->soap_type,
                'plate_numbers' => $plante_numbers,
                'total_charge' => $row->total_charge,
                'filename' => $row->filename,
                'date_of_payment' => $payment,
                'btn_force_send_by_summary' => $row->isAvailableForceSendBySummary(),
                'btn_retention' => $btn_retention,
                'show_packers_in_document' => $show_packers_in_document,
                'show_dispatchers_in_document' => $show_dispatchers_in_document,
                'show_box_in_document' => $show_box_in_document,
            ];
        });
    }


    private function getDispatches($row)
    {

        $dispatches = [];

        if (in_array($row->document_type_id, ['01', '03'])) {

            // $dispatches = DB::connection('tenant')->table('dispatches')
            //     ->select('series', 'number')
            //     ->whereIn('state_type_id', ['01', '03', '05', '07', '55'])
            //     ->where('reference_document_id', $row->id)
            //     ->get()
            $dispatches = $row->reference_guides->whereIn('state_type_id', ['01', '03', '05', '07', '55'])
                ->transform(function ($row) {
                    return [
                        'description' => $row->series . '-' . $row->number,
                    ];
                });

            if ($row->dispatch) {
                $dispatches = $dispatches->push([
                    'description' => $row->dispatch->series . '-' . $row->dispatch->number,
                ]);
            }
        }

        return $dispatches;
    }

    private function getVoided($row, $document_id)
    {
        $voided_data = [];

        if (in_array($row->document_type_id, ['01', '03'])) {
            $voided = \App\Models\Tenant\VoidedDocument::where("document_id", "=", $document_id)->orderBy('id', 'desc')->first();
            if ($voided) {
                $voided_data = \App\Models\Tenant\Voided::where("id", $voided["voided_id"])->orderBy('id', 'desc')->get()->transform(function ($row) {
                    return [
                        'external_id' => $row->external_id,
                        'ticket' => $row->ticket,
                    ];
                });
            }
        }

        return $voided_data;
    }
}

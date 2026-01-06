<?php

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\BankAccount;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Dispatch;
use App\Models\Tenant\EmailSendLog;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\Tenant\Person;
use App\Models\Tenant\SaleNoteMigration;
use App\Models\Tenant\SaleNotePayment;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\DB;
use Modules\BusinessTurn\Models\BusinessTurn;

/**
 * Class SaleNoteCollection
 *
 * @package App\Http\Resources\Tenant
 */
class SaleNoteCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array|\Illuminate\Support\Collection
     */
    public function toArray($request)
    {
        $configuration = Configuration::getConfig();
        $sale_notes_ids = $this->collection->pluck('id');
        $customer_ids = $this->collection->pluck('customer_id');
        $isIntegrateSystem = BusinessTurn::isIntegrateSystem();

        $usersData = DB::connection('tenant')
            ->table('users')
            ->select('id', 'name', 'type')
            ->get()
            ->keyBy('id');
        $saleNoteFeeData = DB::connection('tenant')
            ->table('sale_note_fee')
            ->select('sale_note_id', 'payment_method_type_id')
            ->whereIn('sale_note_id', $sale_notes_ids)
            ->get()
            ->groupBy('sale_note_id');
        $saleNotePaymentData = DB::connection('tenant')
            ->table('sale_note_payments')
            ->select('sale_note_payments.id', 'sale_note_payments.sale_note_id', 'sale_note_payments.payment', 'sale_note_payments.date_of_payment', 'sale_note_payments.payment_method_type_id', 'sale_note_payments.reference')
            ->whereIn('sale_note_id', $sale_notes_ids);
        if ($isIntegrateSystem) {
            $saleNotePaymentData
                ->leftJoin('global_payments', function ($join) {
                    $join->on('sale_note_payments.id', '=', 'global_payments.payment_id')
                        ->whereRaw('global_payments.payment_type = ?', [SaleNotePayment::class]);
                })
                ->leftJoin('payment_files', function ($join) {
                    $join->on('sale_note_payments.id', '=', 'payment_files.payment_id')
                        ->whereRaw('payment_files.payment_type = ?', [SaleNotePayment::class]);
                })
                ->leftJoin('bank_accounts', function ($join) {
                    $join->on('global_payments.destination_id', '=', 'bank_accounts.id')
                        ->whereRaw('global_payments.destination_type = ?', [BankAccount::class]);
                })
                ->leftJoin('banks', 'bank_accounts.bank_id', '=', 'banks.id')
                ->addSelect('banks.description as bank_description', 'bank_accounts.id as bank_account_id', 'banks.id as bank_id', 'payment_files.id as payment_file_id');
        }
        $saleNotePaymentData = $saleNotePaymentData->get()
            ->groupBy('sale_note_id');
        $paymentMethodTypeData = DB::connection('tenant')
            ->table('payment_method_types')
            ->select('id', 'description')
            ->get()
            ->keyBy('id');
        $addressDispatchData = DB::connection('tenant')
            ->table('dispatch_addresses')
            ->select('id', 'person_id', 'location_id', 'reason')
            ->whereIn('person_id', $customer_ids)
            ->orderBy('id', 'asc')
            ->get()
            ->groupBy('person_id')
            ->map(function($addresses) {
                return $addresses->first(); // Traer el primero (más antiguo) de cada customer
            });
        // Optimización: Obtener emails procesados directamente
        $personData = DB::connection('tenant')
            ->table('persons')
            ->select('id', 'email', 'optional_email', 'telephone')
            ->whereIn('id', $customer_ids)
            ->get()
            ->keyBy('id')
            ->map(function ($person) {
                // Procesar optional_email_send aquí mismo
                $optional_mail = $this->processOptionalEmail($person->optional_email);
                $optional_mail_send = [];

                if (!empty($person->email)) {
                    $optional_mail_send[] = $person->email;
                }

                $total_optional_mail = count($optional_mail);
                for ($i = 0; $i < $total_optional_mail; $i++) {
                    $temp = trim($optional_mail[$i]['email']);
                    if (!empty($temp) && $temp != $person->email) {
                        $optional_mail_send[] = $temp;
                    }
                }

                return [
                    'id' => $person->id,
                    'email' => $person->email,
                    'telephone' => $person->telephone,
                    'optional_email_send' => implode(',', $optional_mail_send)
                ];
            });

        $allItemIds = DB::connection('tenant')->table('sale_note_items')->select('item_id')->whereIn('sale_note_id', $sale_notes_ids)->get()->pluck('item_id')->unique();

        $documentsData = DB::connection('tenant')->table('documents')->select('id', 'sale_note_id', 'series', 'number', 'external_id')->whereIn('sale_note_id', $sale_notes_ids)->get()->groupBy('sale_note_id');
        $user = auth()->user();
        $itemsWithPlatforms = DB::connection('tenant')
            ->table('items')
            ->leftJoin('web_platforms', 'items.web_platform_id', '=', 'web_platforms.id')
            ->select(
                'items.id',
                'items.name',
                'items.internal_id',
                'web_platforms.id as web_platform_id',
                'web_platforms.name as web_platform_name'
            )
            ->whereIn('items.id', $allItemIds)
            ->get()
            ->keyBy('id');
        $platformData = collect();
        if ($configuration->show_web_platform_document_sale_note) {
            $platformData = $itemsWithPlatforms->pluck('web_platform_name', 'id')
                ->filter();
            // ->unique();
        }

        // Cargar production orders con sus relaciones
        $productionOrdersData = collect();
        $productionOrdersData = DB::connection('tenant')
            ->table('production_orders')
            ->leftJoin('state_production_orders', 'production_orders.production_order_state_id', '=', 'state_production_orders.id')
            ->leftJoin('users', 'production_orders.responsible_id', '=', 'users.id')
            ->select(
                'production_orders.id',
                'production_orders.prefix as series',
                'production_orders.number',
                'production_orders.responsible_id',
                'state_production_orders.id as state_id',
                'state_production_orders.description as state_description',
                'users.name as responsible_name',
                'production_orders.sale_note_id'
            )
            ->whereIn('sale_note_id', $sale_notes_ids)
            ->get()
            ->keyBy('sale_note_id');
        // Cargar dispatch orders con sus relaciones
        $dispatchOrdersData = collect();
        $dispatchOrdersData = DB::connection('tenant')
            ->table('dispatch_orders')
            ->leftJoin('state_dispatch_orders', 'dispatch_orders.dispatch_order_state_id', '=', 'state_dispatch_orders.id')
            ->leftJoin('users', 'dispatch_orders.responsible_id', '=', 'users.id')
            ->select(
                'dispatch_orders.id',
                'dispatch_orders.prefix as series',
                'dispatch_orders.number',
                'dispatch_orders.responsible_id',
                'state_dispatch_orders.id as state_id',
                'state_dispatch_orders.description as state_description',
                'users.name as responsible_name',
                'dispatch_orders.sale_note_id'
            )
            ->whereIn('sale_note_id', $sale_notes_ids)
            ->get()
            ->keyBy('sale_note_id');

        $dispatchesData = DB::connection('tenant')
            ->table('dispatches')
            ->select('id', 'series', 'number', 'external_id', 'reference_sale_note_id')
            ->whereIn('reference_sale_note_id', $sale_notes_ids)
            ->whereNotIn('state_type_id', [11, 13])
            ->get()
            ->groupBy('reference_sale_note_id')
            ->map(function ($dispatches) {
                return $dispatches->map(function ($dispatch) {
                    return [
                        'id' => $dispatch->id,
                        'number' => $dispatch->series . '-' . $dispatch->number,
                        'external_id' => $dispatch->external_id,
                    ];
                });
            });

        $dispatchOrderIds = $dispatchOrdersData->pluck('id')->filter();
        $agenciesDispatchData = collect();

        if ($dispatchOrderIds->count() > 0) {
            $agenciesDispatchData = DB::connection('tenant')
                ->table('agencies_dispatches_table')
                ->select('dispatch_order_id')
                ->whereIn('dispatch_order_id', $dispatchOrderIds)
                ->get()
                ->pluck('dispatch_order_id')
                ->unique();
        }

        return $this->collection->transform(function ($row, $key) use (
            $configuration,
            $user,
            $documentsData,
            $platformData,
            $personData,
            $saleNotePaymentData,
            $productionOrdersData,
            $dispatchOrdersData,
            $dispatchesData,
            $agenciesDispatchData,
            $usersData,
            $paymentMethodTypeData,
            $saleNoteFeeData,
            $addressDispatchData,
            $isIntegrateSystem
        ) {
            $customer = $row->customer;
            $addressDispatch = $addressDispatchData->get($row->customer_id);
            $location_id = null;
            $department_name = null;
            $province_name = null;
            $district_name = null;
            $state_sale_note_order_id = null;
            
            // Verificar si existe el address dispatch (no es null)
            if ($isIntegrateSystem && $addressDispatch) {
                try {
                    $location_id = $addressDispatch->location_id;
                    $state_sale_note_order_id = $addressDispatch->reason;
                    
                    if ($location_id) {
                        if (is_string($location_id)) {
                            $location_id = json_decode($location_id, true);
                        }
                        $district_id = $location_id[2];
                        $location = func_get_location($district_id);
                        $split_location = explode(' - ', $location);
                        $department_name = $split_location[2];
                        $province_name = $split_location[1];
                        $district_name = $split_location[0];
                    }
                } catch(\Exception $e) {
                    // Error al procesar location_id
                    $location_id = null;
                }
            } else {
                $department_name = $customer->department->description;
            }
            $connection = DB::connection('tenant');
            $edit_payment = false;
            if ($user->edit_payment) {
                $edit_payment = true;
            }
            $btn_guide = true;
            $btn_order_delivery = true;
            $can_be_order_concrete = false;
            $can_change_delivery_state = $user->edit_delivery_state || in_array($user->type, ['admin', 'superadmin']);
            if (in_array($row->state_type_id, ['01', '05'])) {
                $can_be_order_concrete = true;
            }


            $payments = $saleNotePaymentData->get($row->id, collect());
            $total_paid = number_format($payments->sum('payment'), 2, '.', '');
            $total_pending_paid = number_format($row->total - $total_paid, 2, '.', '');
            $document_id = $row->document_id;
            // Normalmente, un documento tendrá el id de la NV,
            // cuando se hace un CPE a partir de varias NV,
            // se guarda el id del documento en el NV
            /** @var Collection $documents */
            $documents = $documentsData->get($row->id);


            if (($documents == null || $documents->count() < 1) && !empty($document_id)) {
                $documents = DB::connection('tenant')->table('documents')->select('id', 'series', 'number', 'external_id')->where('id', $document_id)->get();
            }
            $total_documents = $documents ? $documents->count() : 0;

            $btn_generate = $row->getBtnGenerate($total_documents);
            // $btn_generate = ($total_documents > 0) ? false : true;
            $btn_payments = ($total_documents > 0) ? false : true;
            $due_date = (!empty($row->due_date)) ? $row->due_date->format('Y-m-d') : null;
            $btn_generate = $row->getBtnGenerate($total_documents);
            // $btn_generate = ($total_documents > 0) ? false : true;
            $btn_payments = ($total_documents > 0) ? false : true;
            $due_date = (!empty($row->due_date)) ? $row->due_date->format('Y-m-d') : null;

            if (empty($row->seller_id)) {
                $row->seller_id = $row->user_id;
            }
            // $row->payments = [];
            $message_text = '';
            if (!empty($row->number_full) && !empty($row->external_id)) {
                $message_text = "Su comprobante de nota de venta {$row->number_full} ha sido generado correctamente, puede revisarlo en el siguiente enlace: " .
                    url('') . "/sale-notes/print/{$row->external_id}/a4" . '';
            }
            $canSentToOtherServer = false;
            if ($configuration->isSendDataToOtherServer() == true && auth()->user()->type === 'admin') {
                $alreadySent = SaleNoteMigration::where([
                    'sale_notes_id' => $row->id,
                    'success' => true
                ])->first();
                if ($alreadySent == false) {
                    $canSentToOtherServer = true;
                }
            }
            $child_name = '';
            $child_number = '';

            if (property_exists($customer, 'children')) {
                $child = $customer->children;
                $child_name = $child->name;
                $child_number = $child->number;
            }
            $personInfo = $personData->get($row->customer_id, ['optional_email_send' => '', 'telephone' => '']);
            $customer_email = $personInfo['optional_email_send'];

            if (empty($row->seller_id)) {
                $row->seller_id = $row->user_id;
            }

            $message_text = '';
            if (!empty($row->number_full) && !empty($row->external_id)) {
                $message_text = "Su comprobante de nota de venta {$row->number_full} ha sido generado correctamente, puede revisarlo en el siguiente enlace: " .
                    url('') . "/sale-notes/print/{$row->external_id}/a4" . '';
            }
            $canSentToOtherServer = false;
            if ($configuration->isSendDataToOtherServer() == true && auth()->user()->type === 'admin') {
                $alreadySent = SaleNoteMigration::where([
                    'sale_notes_id' => $row->id,
                    'success' => true
                ])->first();
                if ($alreadySent == false) {
                    $canSentToOtherServer = true;
                }
            }
            $child_name = '';
            $child_number = '';
            $customer = $row->customer;
            if (property_exists($customer, 'children')) {
                $child = $customer->children;
                $child_name = $child->name;
                $child_number = $child->number;
            }

            $date_of_pay = '';
            if ($payments->count() > 0) {
                $date_of_pay = $payments->last()->date_of_payment;
            }
            $not_blocked = true;
            $type_user = $user->type;
            if ($configuration->block_seller_sale_note_edit && $type_user === 'seller') {
                $not_blocked = false;
            }
            $production_order = null;
            $productionOrderData = $productionOrdersData->get($row->id);
            if ($productionOrderData) {
                $production_order = [
                    'id' => $productionOrderData->id,
                    'number_full' => $productionOrderData->series . '-' . $productionOrderData->number,
                    'state_description' => $productionOrderData->state_description,
                    'state_id' => $productionOrderData->state_id,
                    'responsible_name' => $productionOrderData->responsible_name,
                ];
            }
            $dispatch_order = null;
            $dispatchOrderData = $dispatchOrdersData->get($row->id);
            if ($dispatchOrderData) {
                $dispatch_order = [
                    'id' => $dispatchOrderData->id,
                    'number_full' => $dispatchOrderData->series . '-' . $dispatchOrderData->number,
                    'state_description' => $dispatchOrderData->state_description,
                    'state_id' => $dispatchOrderData->state_id,
                    'responsible_name' => $dispatchOrderData->responsible_name,
                ];
            }
            $dispatches = $dispatchesData->get($row->id, collect());
            $has_agency_dispatch = null;
            if ($dispatch_order && $agenciesDispatchData->contains($dispatch_order['id'])) {
                $has_agency_dispatch = true;
            }
            $payments_methods = [];
            $has_complete_payments = true;

            if ($isIntegrateSystem && $row->payment_condition_id == "01") {
                if ($total_pending_paid <= 0) {
                    $saleNotePaymentDatainfo = $saleNotePaymentData->get($row->id, collect());
                    foreach ($saleNotePaymentDatainfo as $paymentInfo) {
                        if (!$paymentInfo->reference || !$paymentInfo->payment_file_id) {
                            $has_complete_payments = false;
                            break;
                        }
                    }
                } else {
                    $has_complete_payments = false;
                }
            }
            if ($row->payment_condition_id == "01") {

                $payments_methods = $saleNotePaymentData->get($row->id, collect())->map(function ($row) use ($paymentMethodTypeData) {
                    return $paymentMethodTypeData->get($row->payment_method_type_id)->description;
                });
                if (count($payments_methods) == 0) {
                    $payment_method_type = $paymentMethodTypeData->get($row->payment_method_type_id);
                    if ($payment_method_type) {
                        $payments_methods = [$payment_method_type->description];
                    }
                }
            } else {
                $payments_methods = $saleNoteFeeData->get($row->id, collect())->map(function ($row) use ($paymentMethodTypeData) {
                    if ($row->payment_method_type_id) {
                        return $paymentMethodTypeData->get($row->payment_method_type_id)->description;
                    } else {
                        return 'Credito';
                    }
                });
            }
            $alter_company = [];
            if ($configuration->multi_companies) {
                if ($row->website_id) {
                    $company = $connection->table('companies')
                        ->select('id', 'name', 'number')
                        ->where('website_id', $row->website_id)
                        ->first();
                } else {
                    $company = $connection->table('companies')
                        ->select('id', 'name', 'number')
                        ->first();
                }
                $alter_company['name'] = $company->name;
                $alter_company['number'] = $company->number;
            }
            $credit_days = "-";
            if ($row->fee) {
                $last_fee = $row->fee->last();
                if ($last_fee) {
                    $credit_days = $last_fee->date;
                }
            }
            $license_plate = $row->license_plate;
            $modify_sale_unit_price = $row->items->where('modify_sale_unit_price', true)->count() > 0 || $row->total_discount > 0;
            if ($configuration->plate_number_config) {
                $plante_numbers_description = optional($row->plateNumberDocument)->plateNumber ? optional($row->plateNumberDocument)->plateNumber->description : null;
                $license_plate = $plante_numbers_description;
            }
            $platforms = "";
            if ($configuration->show_web_platform_document_sale_note) {
                if ($row->relationLoaded('items')) {
                    $itemIds = $row->items->pluck('item_id')->unique();
                    $platforms = $platformData->only($itemIds)->implode(' / ');
                }
            }
            if ($row->no_stock) {
                $no_stock_document = $row->no_stock_document;
                if ($no_stock_document && $no_stock_document->completed) {
                    $btn_guide = false;
                    $btn_order_delivery = false;
                }
            }
            if ($user->create_order_delivery == false) {
                $btn_order_delivery = false;
            }

            $show_packers_document = $user->show_packers_document;
            $show_dispatchers_document = $user->show_dispatchers_document;
            $show_box = $user->show_box;



            return [
                'department_name' => $department_name,
                'province_name' => $province_name,
                'district_name' => $district_name,
                'has_complete_payments' => $has_complete_payments,
                'payment_condition_id' => $row->payment_condition_id,
                'state_sale_note_order' => $state_sale_note_order_id,
                'edit_payment' => $edit_payment,
                'show_packers_document' => $show_packers_document,
                'show_dispatchers_document' => $show_dispatchers_document,
                'show_box' => $show_box,
                'btn_guide' => $btn_guide,
                'btn_order_delivery' => $btn_order_delivery,
                'create_order_delivery' => auth()->user()->create_order_delivery,
                'voided_sale_note' => auth()->user()->voided_sale_note,
                'platforms' => $platforms,
                'can_change_delivery_state' => $can_change_delivery_state,
                'state_delivery_id' => $row->state_delivery_id ?? 1,
                'can_be_order_concrete' => $can_be_order_concrete,
                'credit_days' => $credit_days,
                'modify_sale_unit_price' => $modify_sale_unit_price,
                'box' => $row->box,
                'shipping_address' => $row->shipping_address,
                'dispatcher_id' => $row->dispatcher_id,
                'person_packer_id' => $row->person_packer_id,
                'person_dispatcher_id' => $row->person_dispatcher_id,
                'alter_company' => $alter_company,
                'website_id' => $row->website_id,
                'payments_methods' => $payments_methods,
                'quotation_id' => $row->quotation_id,
                'has_agency_dispatch' => $has_agency_dispatch,
                'state_payment_id' => $row->state_payment_id,
                'dispatches' => $dispatches,
                'production_order'             => $production_order,
                'dispatch_order'               => $dispatch_order,
                'not_blocked' => $not_blocked,
                'id' => $row->id,
                'soap_type_id' => $row->soap_type_id,
                'external_id' => $row->external_id,
                'date_of_issue' => $row->date_of_issue->format('Y-m-d'),
                'time_of_issue' => $row->time_of_issue,
                'identifier' => $row->identifier,
                'full_number' => $row->series . '-' . $row->number,
                'customer_name' => $customer->name,
                'customer_id' => $row->customer_id,
                'customer_number' => $customer->number,
                'children_name' => $child_name,
                'children_number' => $child_number,
                'exchange_rate_sale' => $row->exchange_rate_sale,
                'currency_type_id' => $row->currency_type_id,
                'total_exportation' => self::FormatNumber($row->total_exportation),
                'total_free' => self::FormatNumber($row->total_free),
                'total_unaffected' => self::FormatNumber($row->total_unaffected),
                'total_exonerated' => self::FormatNumber($row->total_exonerated),
                'total_taxed' => self::FormatNumber($row->total_taxed),
                'total_igv' => self::FormatNumber($row->total_igv),
                'total' => self::FormatNumber($row->total),
                'state_type_id' => $row->state_type_id,
                'observation' => $row->observation,
                'state_type_description' => $row->state_type->description,
                'document_id' => $row->document_id,
                'documents' =>  $documents ? $documents->transform(function ($row) {
                    $email_send_it = false;
                    $send_it = EmailSendLog::Document()->FindRelationId($row->id)->get();
                    if (count($send_it) > 0) {
                        $email_send_it = true;
                    }
                    return [
                        'id' => $row->id,
                        'number_full' => $row->series . '-' . $row->number,
                        'email_send_it' => $email_send_it,
                    ];
                }) : [],
                'btn_generate' => $btn_generate,
                'btn_payments' => $btn_payments,
                'changed' => (bool)$row->changed,
                'enabled_concurrency' => (bool)$row->enabled_concurrency,
                'quantity_period' => $row->quantity_period,
                'type_period' => $row->type_period,
                'apply_concurrency' => (bool)$row->apply_concurrency,
                'created_at' => $row->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $row->updated_at->format('Y-m-d H:i:s'),
                'paid' => (bool)$row->paid,
                'total_canceled' => (bool)$row->total_canceled,
                'license_plate' => $license_plate,
                'total_paid' => $total_paid,
                'total_pending_paid' => $total_pending_paid,
                'user_name' => ($row->user) ? $row->user->name : '',
                'has_user_rel_suscription_plan' => $row->user_rel_suscription_plan_id > 0,
                'quotation_number_full' => ($row->quotation) ? $row->quotation->number_full : '',
                'sale_opportunity_number_full' => isset($row->quotation->sale_opportunity)
                    ? $row->quotation->sale_opportunity->number_full : '',
                'number_full' => $row->number_full,
                'print_a4' => url('') . "/sale-notes/print/{$row->external_id}/a4",
                'print_ticket' => url('') . "/sale-notes/print/{$row->external_id}/ticket",
                'print_a5' => url('') . "/sale-notes/print/{$row->external_id}/a5",
                'print_receipt' => url('') . "/sale-notes/receipt/{$row->id}",
                'print_ticket_58' => url('') . "/sale-notes/print/{$row->external_id}/ticket_58",
                'print_ticket_50' => $row->getUrlPrintByFormat('ticket_50'),
                'purchase_order' => $row->purchase_order,
                'due_date' => $due_date,
                'fee' => $row->fee,
                'seller_id' => $row->seller_id,
                'message_text' => $message_text,
                'serie' => $row->series,
                'number' => $row->number,
                // 'number' => $row->number_full,
                'grade' => $row->getGrade(),
                'section' => $row->getSection(),
                'send_other_server' => $canSentToOtherServer,
                'customer_email' => $customer_email,
                'customer_telephone' => $personInfo['telephone'],
                'filename' => $row->filename,
                'seller_name'                     => ((int)$row->seller_id != 0) ? $usersData->get($row->seller_id)->name : '',
                'date_of_payment'              => $date_of_pay,
                'customer_region'              => $customer->department->description,
                // 'number' => $row->number,
                'agent_name' => optional($row->agent)->search_description,
                'reference_data' => $row->reference_data,
                // 'payments' => $row->payments,

                'total_discount' => $row->generalApplyNumberFormat($row->total_discount),
                'items_for_report' => $row->getItemsforReport(),
                'payments_with_banks' => $isIntegrateSystem ? $payments->map(function ($payment) {
                    return [
                        'payment_method_type_id' => $payment->payment_method_type_id,
                        'payment' => $payment->payment,
                        'date_of_payment' => $payment->date_of_payment,
                        'bank_id' => $payment->bank_id ?? null,
                        'bank_description' => $payment->bank_description ?? null,
                        'bank_account_id' => $payment->bank_account_id ?? null,
                    ];
                })->toArray() : [],

            ];
        });
    }
    public static function FormatNumber($number, $decimal = 2)
    {
        return number_format($number, $decimal, '.', '');
    }

    /**
     * Procesar optional_email desde string serializado
     */
    private function processOptionalEmail($optional_email)
    {
        $data = unserialize($optional_email);
        return ($data === false) ? [] : $data;
    }

    /**
     * Obtener emails procesados de personas
     */
    private function getProcessedPersonEmails($customer_ids)
    {
        $cache_key = 'person_emails_' . md5(implode(',', $customer_ids->toArray()));

        return DB::connection('tenant')
            ->table('persons')
            ->select('id', 'email', 'optional_email')
            ->whereIn('id', $customer_ids)
            ->get()
            ->keyBy('id')
            ->map(function ($person) {
                return $this->processOptionalEmailSend($person->email, $person->optional_email);
            });
    }

    /**
     * Procesar optional_email_send
     */
    private function processOptionalEmailSend($email, $optional_email)
    {
        $optional_mail = $this->processOptionalEmail($optional_email);
        $optional_mail_send = [];

        if (!empty($email)) {
            $optional_mail_send[] = $email;
        }

        foreach ($optional_mail as $mail) {
            $temp = trim($mail['email']);
            if (!empty($temp) && $temp != $email) {
                $optional_mail_send[] = $temp;
            }
        }

        return implode(',', $optional_mail_send);
    }
}

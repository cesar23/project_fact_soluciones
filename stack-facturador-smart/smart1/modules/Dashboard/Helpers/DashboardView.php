<?php

namespace Modules\Dashboard\Helpers;

use App\CoreFacturalo\Requests\Inputs\Functions;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Document;
use App\Models\Tenant\DocumentItem;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Dispatch;
use App\Models\Tenant\DocumentPayment;
use App\Models\Tenant\Item;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\SaleNoteItem;
use App\Models\Tenant\SaleNotePayment;
use App\Models\Tenant\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Item\Models\WebPlatform;

class DashboardView
{
    public static function getEstablishments()
    {
        return Establishment::whereActive()->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'name' => $row->description
            ];
        });
    }

    public static function getUnpaid($request)
    {
        $establishment_id = $request['establishment_id'];
        $period = $request['period'];
        $date_start = $request['date_start'];
        $date_end = $request['date_end'];
        $month_start = $request['month_start'];
        $month_end = $request['month_end'];
        $customer_id = $request['customer_id'];


        $d_start = null;
        $d_end = null;

        /** @todo: Eliminar periodo, fechas y cambiar por

        $date_start = $request['date_start'];
        $date_end = $request['date_end'];
        \App\CoreFacturalo\Helpers\Functions\FunctionsHelper\FunctionsHelper::setDateInPeriod($request, $date_start, $date_end);
         */
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

        /*
         * Documents
         */
        $document_payments = DB::connection('tenant')
            ->table('document_payments')
            ->select(
                'document_id',
                DB::raw('SUM(payment) + COALESCE(
                    (SELECT JSON_UNQUOTE(JSON_EXTRACT(d.retention, "$.amount"))
                    FROM documents d 
                    WHERE d.id = document_payments.document_id 
                    AND d.retention IS NOT NULL), 
                    0
                ) as total_payment')
            )
            ->groupBy('document_id');

        if ($d_start && $d_end) {

            $documents = DB::connection('tenant')
                ->table('documents')
                ->where('customer_id', $customer_id)
                ->join('persons', 'persons.id', '=', 'documents.customer_id')
                ->leftJoinSub($document_payments, 'payments', function ($join) {
                    $join->on('documents.id', '=', 'payments.document_id');
                })
                ->whereIn('state_type_id', ['01', '03', '05', '07', '13'])
                ->whereIn('document_type_id', ['01', '03', '08'])
                ->select(DB::raw("documents.id as id, " .
                    "DATE_FORMAT(documents.date_of_issue, '%Y/%m/%d') as date_of_issue, " .
                    "persons.name as customer_name, persons.id as customer_id, documents.document_type_id," .
                    "CONCAT(documents.series,'-',documents.number) AS number_full, " .
                    "documents.total as total, " .
                    "IFNULL(payments.total_payment, 0) as total_payment, " .
                    "'document' AS 'type', " . "documents.currency_type_id, " . "documents.exchange_rate_sale"))
                ->where('documents.establishment_id', $establishment_id)
                ->whereBetween('documents.date_of_issue', [$d_start, $d_end]);
        } else {

            $documents = DB::connection('tenant')
                ->table('documents')
                ->where('customer_id', $customer_id)
                ->join('persons', 'persons.id', '=', 'documents.customer_id')
                ->leftJoinSub($document_payments, 'payments', function ($join) {
                    $join->on('documents.id', '=', 'payments.document_id');
                })
                ->whereIn('state_type_id', ['01', '03', '05', '07', '13'])
                ->whereIn('document_type_id', ['01', '03', '08'])
                ->select(DB::raw("documents.id as id, " .
                    "DATE_FORMAT(documents.date_of_issue, '%Y/%m/%d') as date_of_issue, " .
                    "persons.name as customer_name, persons.id as customer_id, documents.document_type_id, " .
                    "CONCAT(documents.series,'-',documents.number) AS number_full, " .
                    "documents.total as total, " .
                    "IFNULL(payments.total_payment, 0) as total_payment, " .
                    "'document' AS 'type', " . "documents.currency_type_id, " . "documents.exchange_rate_sale"))
                ->where('documents.establishment_id', $establishment_id);
        }

        /*
         * Sale Notes
         */
        $sale_note_payments = DB::connection('tenant')->table('sale_note_payments')
            ->select('sale_note_id', DB::raw('SUM(payment) as total_payment'))
            ->groupBy('sale_note_id');

        if ($d_start && $d_end) {

            $sale_notes = DB::connection('tenant')
                ->table('sale_notes')
                ->where('customer_id', $customer_id)
                ->join('persons', 'persons.id', '=', 'sale_notes.customer_id')
                ->leftJoinSub($sale_note_payments, 'payments', function ($join) {
                    $join->on('sale_notes.id', '=', 'payments.sale_note_id');
                })
                ->whereIn('state_type_id', ['01', '03', '05', '07', '13'])
                ->select(DB::raw("sale_notes.id as id, " .
                    "DATE_FORMAT(sale_notes.date_of_issue, '%Y/%m/%d') as date_of_issue, " .
                    "persons.name as customer_name, persons.id as customer_id, null as document_type_id," .
                    "sale_notes.filename as number_full, " .
                    "sale_notes.total as total, " .
                    "IFNULL(payments.total_payment, 0) as total_payment, " .
                    "'sale_note' AS 'type', " . "sale_notes.currency_type_id, " . "sale_notes.exchange_rate_sale"))
                ->where('sale_notes.establishment_id', $establishment_id)
                ->where('sale_notes.changed', false)
                ->whereBetween('sale_notes.date_of_issue', [$d_start, $d_end])
                ->where('sale_notes.total_canceled', false);
        } else {

            $sale_notes = DB::connection('tenant')
                ->table('sale_notes')
                ->where('customer_id', $customer_id)
                ->join('persons', 'persons.id', '=', 'sale_notes.customer_id')
                ->leftJoinSub($sale_note_payments, 'payments', function ($join) {
                    $join->on('sale_notes.id', '=', 'payments.sale_note_id');
                })
                ->whereIn('state_type_id', ['01', '03', '05', '07', '13'])
                ->select(DB::raw("sale_notes.id as id, " .
                    "DATE_FORMAT(sale_notes.date_of_issue, '%Y/%m/%d') as date_of_issue, " .
                    "persons.name as customer_name, persons.id as customer_id, null as document_type_id," .
                    "sale_notes.filename as number_full, " .
                    "sale_notes.total as total, " .
                    "IFNULL(payments.total_payment, 0) as total_payment, " .
                    "'sale_note' AS 'type', " . "sale_notes.currency_type_id, " . "sale_notes.exchange_rate_sale"))
                ->where('sale_notes.establishment_id', $establishment_id)
                ->where('sale_notes.changed', false)
                ->where('sale_notes.total_canceled', false);
        }

        $records = $documents->union($sale_notes)->get();

        return collect($records)->transform(function ($row) {
            $total_to_pay = (float)$row->total - (float)$row->total_payment;
            $delay_payment = null;
            $date_of_due = null;

            if ($total_to_pay > 0) {
                if ($row->document_type_id) {

                    $invoice = Invoice::where('document_id', $row->id)->first();
                    if ($invoice) {
                        $due =   Carbon::parse($invoice->date_of_due); // $invoice->date_of_due;
                        $date_of_due = $invoice->date_of_due->format('Y/m/d');
                        $now = Carbon::now();

                        if ($now > $due) {

                            $delay_payment = $now->diffInDays($due);
                        }
                    }
                }
            }

            $guides = null;
            $date_payment_last = '';

            if ($row->document_type_id) {
                $guides =  Dispatch::where('reference_document_id', $row->id)->orderBy('series')->orderBy('number', 'desc')->get()->transform(function ($item) {
                    return [
                        'id' => $item->id,
                        'external_id' => $item->external_id,
                        'number' => $item->number_full,
                        'date_of_issue' => $item->date_of_issue->format('Y-m-d'),
                        'date_of_shipping' => $item->date_of_shipping->format('Y-m-d'),
                        'download_external_xml' => $item->download_external_xml,
                        'download_external_pdf' => $item->download_external_pdf,
                    ];
                });

                $date_payment_last = DocumentPayment::where('document_id', $row->id)->orderBy('date_of_payment', 'desc')->first();
            } else {
                $date_payment_last = SaleNotePayment::where('sale_note_id', $row->id)->orderBy('date_of_payment', 'desc')->first();
            }

            return [
                'id' => $row->id,
                'date_of_issue' => $row->date_of_issue,
                'customer_name' => $row->customer_name,
                'customer_id' => $row->customer_id,
                'number_full' => $row->number_full,
                'total' => number_format((float) $row->total, 2, ".", ""),
                'total_to_pay' => number_format($total_to_pay, 2, ".", ""),
                'type' => $row->type,
                'guides' => $guides,
                'date_payment_last' => ($date_payment_last) ? $date_payment_last->date_of_payment->format('Y-m-d') : null,
                'delay_payment' => $delay_payment,
                'date_of_due' =>  $date_of_due,
                'currency_type_id' => $row->currency_type_id,
                'exchange_rate_sale' => (float)$row->exchange_rate_sale
            ];
            //            }
        });
    }

    public static function getUnpaidFilterUserEspecial($request)
    {
        $configuration = Configuration::first();
        $show_all =  $configuration->show_all_unpaid;
        $establishment_id = $request['establishment_id'] ?? null;
        $period = $request['period'] ?? null;
        $date_start = $request['date_start'] ?? null;
        $date_end = $request['date_end'] ?? null;
        $ubigeo = Functions::valueKeyInArray($request, 'ubigeo');
        $seller_id = Functions::valueKeyInArray($request, 'seller_id');
        $document_number = Functions::valueKeyInArray($request, 'document_number');
        $department_id = null;
        $province_id = null;
        if ($ubigeo) {
            $ubigeo = explode(',', $ubigeo);
            $department_id = $ubigeo[0];
            if (count($ubigeo) > 1) {
                $province_id = $ubigeo[1];
            }
        }
        $month_start = $request['month_start'] ?? null;
        $month_end = $request['month_end'] ?? null;
        $customer_id = $request['customer_id'] ?? null;
        $extern = $request['extern'] ?? null;
        if ($extern == 'true') {
            $establishment_id = auth()->user()->establishment_id;
        }
        $user_id = $request['user_id'] ?? null;
        $web_platform_id = $request['web_platform_id'] ?? 0;
        $purchase_order = $request['purchase_order'] ?? null;
        $zone_id = Functions::valueKeyInArray($request, 'zone_id');
        $payment_method_type_id = $request['payment_method_type_id'] ?? null;
        // Obtendrá todos los establecimientos
        $stablishmentUnpaidAll = $request['stablishmentUnpaidAll'] ?? 0;
        $user = auth()->user();
        if (null === $user) {
            $user = new \App\Models\Tenant\User();
        }
        $user_type = $user->type;
        $user_id_session = $user->id;
        $d_start = null;
        $d_end = null;
        $with_payments = true;
        /** @todo: Eliminar periodo, fechas y cambiar por

        $date_start = $request['date_start'];
        $date_end = $request['date_end'];
        \App\CoreFacturalo\Helpers\Functions\FunctionsHelper\FunctionsHelper::setDateInPeriod($request, $date_start, $date_end);
         */
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
        /*
         * Documents
         */


        $document_payments = DB::connection('tenant')
            ->table('document_payments')
            ->select(
                'document_id',
                DB::raw('SUM(payment) + COALESCE(
                    (SELECT JSON_UNQUOTE(JSON_EXTRACT(d.retention, "$.amount"))
                    FROM documents d 
                    WHERE d.id = document_payments.document_id 
                    AND d.retention IS NOT NULL), 
                    0
                ) as total_payment')
            )
            ->groupBy('document_id');
        $bills_of_exchanges_payments = DB::connection('tenant')
            ->table('bills_of_exchange_payments')
            ->select('bill_of_exchange_id', DB::raw('SUM(payment) as total_payment'))
            ->groupBy('bill_of_exchange_id');
        $bills_of_exchanges_select = "bills_of_exchange.id as id, " .
            "DATE_FORMAT(bills_of_exchange.date_of_due, '%Y/%m/%d') as date_of_issue, " .
            "persons.name as customer_name," .
            "persons.id as customer_id," .
            "null as document_type_id," .
            "CONCAT(bills_of_exchange.series,'-',bills_of_exchange.number) AS number_full, " .
            "bills_of_exchange.total as total, " .
            "IFNULL(payments.total_payment, 0) as total_payment, " .
            "null as total_credit_notes," .
            "bills_of_exchange.total - IFNULL(total_payment, 0)    as total_subtraction, " .
            "'bill_of_exchange' AS 'type', " .
            "null as retention, " .
            "bills_of_exchange.currency_type_id, " .
            "bills_of_exchange.exchange_rate_sale, " .
            " bills_of_exchange.user_id, " .
            "users.name as username";
        $document_select = "documents.id as id, " .
            "DATE_FORMAT(documents.date_of_issue, '%Y/%m/%d') as date_of_issue, " .
            "persons.name as customer_name," .
            "persons.id as customer_id," .
            "documents.document_type_id," .
            "CONCAT(documents.series,'-',documents.number) AS number_full, " .
            "documents.total as total, " .
            "IFNULL(payments.total_payment, 0) as total_payment, " .
            "IFNULL(credit_notes.total_credit_notes, 0) as total_credit_notes, " .
            "documents.total - IFNULL(total_payment, 0)  - IFNULL(total_credit_notes, 0)  as total_subtraction, " .
            "'document' AS 'type', " .
            "documents.retention as retention, " .
            "documents.currency_type_id, " .
            "documents.exchange_rate_sale, " .
            " documents.user_id, " .
            "users.name as username";

        $sale_note_select = "sale_notes.id as id, " .
            "DATE_FORMAT(sale_notes.date_of_issue, '%Y/%m/%d') as date_of_issue, " .
            "persons.name as customer_name," .
            "persons.id as customer_id," .
            "null as document_type_id," .
            "sale_notes.filename as number_full, " .
            "sale_notes.total as total, " .
            "IFNULL(payments.total_payment, 0) as total_payment, " .
            "null as total_credit_notes," .
            "sale_notes.total - IFNULL(total_payment, 0)  as total_subtraction, " .
            "'sale_note' AS 'type', " .
            "null as retention, " .
            "sale_notes.currency_type_id, " .
            "sale_notes.exchange_rate_sale, " .
            " sale_notes.user_id, " .
            "users.name as username";
            $document_fees_select = "document_fee.id as id, " .
            "DATE_FORMAT(document_fee.date, '%Y/%m/%d') as date_of_issue, " .
            "persons.name as customer_name," .
            "persons.id as customer_id," .
            "documents.document_type_id," .
            "CONCAT(documents.series,'-',documents.number,'-',document_fee.id) AS number_full, " .
            "IFNULL(document_fee.original_amount, document_fee.amount) as total, " .
            "CASE WHEN document_fee.is_canceled = 1 THEN document_fee.original_amount ELSE (document_fee.original_amount - document_fee.amount) END as total_payment, " .
            "0 as total_credit_notes, " .
            "CASE WHEN document_fee.is_canceled = 1 THEN 0 ELSE document_fee.amount END as total_subtraction, " .
            "'document_fee' AS 'type', " .
            "documents.retention as retention, " .
            "documents.currency_type_id, " .
            "documents.exchange_rate_sale, " .
            "documents.user_id, " .
            "users.name as username";

        $documents_fees = DB::connection('tenant')
            ->table('documents')
            ->join('persons', 'persons.id', '=', 'documents.customer_id')
            ->join('users', 'users.id', '=', 'documents.user_id')
            ->join('document_fee', 'documents.id', '=', 'document_fee.document_id')
            ->whereIn('state_type_id', ['01', '03', '05', '07', '13'])
            ->whereIn('document_type_id', ['01', '03', '08'])
            ->where('payment_condition_id', '02')
            ->where('document_fee.is_canceled', false);
    
            $documents_fees = $documents_fees->select(DB::raw($document_fees_select));

        $bills_of_exchange = DB::connection('tenant')
            ->table('bills_of_exchange')
            //->where('customer_id', $customer_id)
            ->join('persons', 'persons.id', '=', 'bills_of_exchange.customer_id')
            ->join('users', 'users.id', '=', 'bills_of_exchange.user_id')
            ->leftJoinSub($bills_of_exchanges_payments, 'payments', function ($join) {
                $join->on('bills_of_exchange.id', '=', 'payments.bill_of_exchange_id');
            });
        
            $bills_of_exchange = $bills_of_exchange->select(DB::raw($bills_of_exchanges_select));

        $documents = DB::connection('tenant')
            ->table('documents')
            ->join('persons', 'persons.id', '=', 'documents.customer_id')
            ->join('users', 'users.id', '=', 'documents.user_id')
            ->leftJoinSub($document_payments, 'payments', function ($join) {
                $join->on('documents.id', '=', 'payments.document_id');
            })
            ->leftJoinSub(Document::getQueryCreditNotes(), 'credit_notes', function ($join) {
                $join->on('documents.id', '=', 'credit_notes.affected_document_id');
            })
            ->whereNotExists(function ($query) {
                $query->select(DB::raw('sale_notes.id'))
                    ->from('sale_notes')
                    ->whereRaw('sale_notes.id = documents.sale_note_id')
                    ->where(function ($query) {
                        $query->where('sale_notes.total_canceled', true)
                            ->orWhere('sale_notes.paid', true);
                    });
            })
            ->whereIn('state_type_id', ['01', '03', '05', '07', '13'])
            ->whereIn('document_type_id', ['01', '03', '08'])
            ->where('payment_condition_id', '01');

        
            $documents = $documents->select(DB::raw($document_select));

        if ($stablishmentUnpaidAll !== 1 && $stablishmentUnpaidAll !== "1") {
            $documents->where('documents.establishment_id', $establishment_id);
        }

        if ($payment_method_type_id) {
            $documents->where('payment_method_type_id', $payment_method_type_id);
        }
        $documents->whereNull('bill_of_exchange_id');

        $sale_note_payments = DB::connection('tenant')
            ->table('sale_note_payments')
            ->select('sale_note_id', DB::raw('SUM(payment) as total_payment'))
            ->groupBy('sale_note_id');

        $sale_notes = DB::connection('tenant')
            ->table('sale_notes')
            ->join('persons', 'persons.id', '=', 'sale_notes.customer_id')
            ->join('users', 'users.id', '=', 'sale_notes.user_id')
            ->leftJoinSub($sale_note_payments, 'payments', function ($join) {
                $join->on('sale_notes.id', '=', 'payments.sale_note_id');
            })
            ->whereIn('state_type_id', ['01', '03', '05', '07', '13'])
            ->select(DB::raw($sale_note_select))
            ->where('sale_notes.changed', false)

            ->where('sale_notes.total_canceled', false);

            if ($document_number) {
                $sale_notes->where('sale_notes.number', 'like', '%' . $document_number . '%');
            }
            

        if ($stablishmentUnpaidAll !== 1 && $stablishmentUnpaidAll !== "1") {
            $sale_notes->where('sale_notes.establishment_id', $establishment_id);
        }

        if (!$show_all) {
            if ($user_type == 'seller') { // Línea modificada
                $sale_notes->where('user_id', $user_id_session);
                $documents->where('user_id', $user_id_session);
            }
        }

        if ($user_id) {
            $sale_notes->where('user_id', $user_id);
            $documents->where('user_id', $user_id);
            $bills_of_exchange->where('user_id', $user_id);
            $documents_fees->where('user_id', $user_id);
        }
        if ($customer_id) {
            $sale_notes->where('customer_id', $customer_id);
            $documents->where('customer_id', $customer_id);
            $bills_of_exchange->where('customer_id', $customer_id);
            $documents_fees->where('customer_id', $customer_id);
        }
        if ($zone_id) {
            $sale_notes->where('persons.zone_id', $zone_id);
            $documents->where('persons.zone_id', $zone_id);
            $bills_of_exchange->where('persons.zone_id', $zone_id);
            $documents_fees->where('persons.zone_id', $zone_id);
        }
        if ($department_id) {
            if ($province_id) {
                $sale_notes->where('persons.province_id', $province_id);
                $documents->where('persons.province_id', $province_id);
                $bills_of_exchange->where('persons.province_id', $province_id);
                $documents_fees->where('persons.province_id', $province_id);
            } else {
                $sale_notes->where('persons.department_id', $department_id);
                $documents->where('persons.department_id', $department_id);
                $bills_of_exchange->where('persons.department_id', $department_id);
                $documents_fees->where('persons.department_id', $department_id);
            }
        }
        if ($seller_id) {
            $sale_notes->where('sale_notes.seller_id', $seller_id);
            $documents->where('documents.seller_id', $seller_id);
            $bills_of_exchange->where('bills_of_exchange.user_id', $seller_id);
            $documents_fees->where('documents.seller_id', $seller_id);
        }
        if ($d_start && $d_end) {
            $sale_notes->whereBetween('sale_notes.date_of_issue', [$d_start, $d_end]);
            $documents->whereBetween('documents.date_of_issue', [$d_start, $d_end]);
            $documents_fees->whereBetween('documents.date_of_issue', [$d_start, $d_end]);
            $bills_of_exchange->whereBetween('bills_of_exchange.created_at', [$d_start, $d_end]);
        }
        if ($purchase_order !== null) {
            $documents->where('purchase_order', $purchase_order);
            $sale_notes->where('purchase_order', $purchase_order);
        }
        if ($web_platform_id != 0) {
            $web_platform_table_name = (new WebPlatform())->getTable();
            $item_table_name = (new Item())->getTable();
            $document_item_table = (new DocumentItem())->getTable();
            $sale_note_item_table = (new SaleNoteItem())->getTable();
            $document_items_id = DocumentItem::leftJoin($item_table_name, "$item_table_name.id", '=', "$document_item_table.item_id")
                ->leftJoin($web_platform_table_name, "$web_platform_table_name.id", '=', "$item_table_name.web_platform_id")
                ->where("$item_table_name.web_platform_id", $web_platform_id)
                ->select($document_item_table . '.document_id as document_id')
                ->get()
                ->pluck('document_id');
            $documents->wherein('documents.id', $document_items_id);

            $sale_note_items_id = SaleNoteItem::leftJoin($item_table_name, "$item_table_name.id", '=', "$sale_note_item_table.item_id")
                ->leftJoin($web_platform_table_name, "$web_platform_table_name.id", '=', "$item_table_name.web_platform_id")
                ->where("$item_table_name.web_platform_id", $web_platform_id)
                ->select($sale_note_item_table . '.sale_note_id as document_id')
                ->get()
                ->pluck('document_id');
            $sale_notes->wherein('sale_notes.id', $sale_note_items_id);
        }

        if ($document_number) {
            $documents_fees->where('documents.number', 'like', '%' . $document_number . '%');
            $bills_of_exchange->where('bills_of_exchange.number', 'like', '%' . $document_number . '%');
            $documents->where('documents.number', 'like', '%' . $document_number . '%');
            $sale_notes->where('sale_notes.number', 'like', '%' . $document_number . '%');
        }


    

        $documents = $documents->havingRaw('CAST(total_subtraction AS DECIMAL(12,2)) > 0');
        $sale_notes = $sale_notes->havingRaw('CAST(total_subtraction AS DECIMAL(12,2)) > 0');
        $bills_of_exchange = $bills_of_exchange->havingRaw('CAST(total_subtraction AS DECIMAL(12,2)) > 0');
        $documents_fees = $documents_fees->havingRaw('CAST(total_subtraction AS DECIMAL(12,2)) > 0');

        return $documents->union($documents_fees)
            ->union($sale_notes)
            ->union($bills_of_exchange);
    }

    public static function getToPayFilterUserEspecial($request)
    {
        $configuration = Configuration::first();
        $establishment_id = $request['establishment_id'] ?? null;
        $period = $request['period'] ?? null;
        $date_start = $request['date_start'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $date_end = $request['date_end'] ?? Carbon::now()->endOfMonth()->format('Y-m-d');
        $month_start = $request['month_start'] ?? Carbon::now()->format('Y-m');
        $month_end = $request['month_end'] ?? Carbon::now()->format('Y-m');
        $supplier_id = $request['supplier_id'] ?? null;
        $user_id = $request['user_id'] ?? null;
        $stablishmentToPayAll = $request['stablishmentTopaidAll'] ?? 0;

        $user = auth()->user();
        if (null === $user) {
            $user = new \App\Models\Tenant\User();
        }

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

        /*
         * Purchase Payments
         */
        $purchase_payments = DB::connection('tenant')->table('purchase_payments')
            ->select('purchase_id', DB::raw('SUM(payment) as total_payment'))
            ->groupBy('purchase_id');

        $bills_of_exchanges_payments = DB::connection('tenant')
            ->table('bills_of_exchange_payments_pay')
            ->select('bill_of_exchange_id', DB::raw('SUM(payment) as total_payment'))
            ->groupBy('bill_of_exchange_id');

        $expense_payments = DB::connection('tenant')->table('expense_payments')
            ->select('expense_id', DB::raw('SUM(payment) as total_payment'))
            ->groupBy('expense_id');

        /*
         * Purchases
         */
        $purchase_select = "purchases.id as id, " .
            "DATE_FORMAT(purchases.date_of_issue, '%Y/%m/%d') as date_of_issue, " .
            "DATE_FORMAT(purchases.date_of_due, '%Y/%m/%d') as date_of_due, " .
            "persons.name as supplier_name," .
            "persons.id as supplier_id," .
            "persons.number as supplier_ruc," .
            "persons.telephone as supplier_telephone," .
            "purchases.document_type_id," .
            "CONCAT(purchases.series,'-',purchases.number) AS number_full, " .
            "purchases.total as total, " .
            "IFNULL(payments.total_payment, 0) as total_payment, " .
            "purchases.total - IFNULL(payments.total_payment, 0) as total_subtraction, " .
            "'purchase' AS 'type', " .
            "purchases.currency_type_id, " .
            "purchases.exchange_rate_sale, " .
            "purchases.user_id, " .
            "users.name as username";

        $purchases = DB::connection('tenant')
            ->table('purchases')
            ->join('persons', 'persons.id', '=', 'purchases.supplier_id')
            ->join('users', 'users.id', '=', 'purchases.user_id')
            ->leftJoinSub($purchase_payments, 'payments', function ($join) {
                $join->on('purchases.id', '=', 'payments.purchase_id');
            })
            ->whereIn('state_type_id', ['01', '03', '05', '07', '13'])
            ->whereIn('document_type_id', ['01', '03', 'GU75', 'NE76'])
            ->where('payment_condition_id', '01')
            ->whereNull('bill_of_exchange_pay_id')
            ->where('total', '>', 0);

        $purchases = $purchases->select(DB::raw($purchase_select));

        /*
         * Bills of Exchange Pay
         */
        $bills_of_exchange_select = "bills_of_exchange_pay.id as id, " .
            "DATE_FORMAT(bills_of_exchange_pay.date_of_due, '%Y/%m/%d') as date_of_issue, " .
            "DATE_FORMAT(bills_of_exchange_pay.date_of_due, '%Y/%m/%d') as date_of_due, " .
            "persons.name as supplier_name," .
            "persons.id as supplier_id," .
            "persons.number as supplier_ruc," .
            "persons.telephone as supplier_telephone," .
            "null as document_type_id," .
            "CONCAT(bills_of_exchange_pay.series,'-',bills_of_exchange_pay.number) AS number_full, " .
            "bills_of_exchange_pay.total as total, " .
            "IFNULL(payments.total_payment, 0) as total_payment, " .
            "bills_of_exchange_pay.total - IFNULL(payments.total_payment, 0) as total_subtraction, " .
            "'bill_of_exchange' AS 'type', " .
            "bills_of_exchange_pay.currency_type_id, " .
            "bills_of_exchange_pay.exchange_rate_sale, " .
            "bills_of_exchange_pay.user_id, " .
            "users.name as username";

        $bills_of_exchange = DB::connection('tenant')
            ->table('bills_of_exchange_pay')
            ->join('persons', 'persons.id', '=', 'bills_of_exchange_pay.supplier_id')
            ->join('users', 'users.id', '=', 'bills_of_exchange_pay.user_id')
            ->leftJoinSub($bills_of_exchanges_payments, 'payments', function ($join) {
                $join->on('bills_of_exchange_pay.id', '=', 'payments.bill_of_exchange_id');
            });

        $bills_of_exchange = $bills_of_exchange->select(DB::raw($bills_of_exchange_select));

        /*
         * Expenses
         */
        $expense_select = "expenses.id as id, " .
            "DATE_FORMAT(expenses.date_of_issue, '%Y/%m/%d') as date_of_issue, " .
            "null as date_of_due, " .
            "persons.name as supplier_name," .
            "persons.id as supplier_id," .
            "persons.number as supplier_ruc," .
            "persons.telephone as supplier_telephone," .
            "null as document_type_id," .
            "expenses.number as number_full, " .
            "expenses.total as total, " .
            "IFNULL(payments.total_payment, 0) as total_payment, " .
            "expenses.total - IFNULL(payments.total_payment, 0) as total_subtraction, " .
            "'expense' AS 'type', " .
            "expenses.currency_type_id, " .
            "expenses.exchange_rate_sale, " .
            "expenses.user_id, " .
            "users.name as username";

        $expenses = DB::connection('tenant')
            ->table('expenses')
            ->join('persons', 'persons.id', '=', 'expenses.supplier_id')
            ->join('users', 'users.id', '=', 'expenses.user_id')
            ->leftJoinSub($expense_payments, 'payments', function ($join) {
                $join->on('expenses.id', '=', 'payments.expense_id');
            })
            ->whereIn('state_type_id', ['01', '03', '05', '07', '13']);

        $expenses = $expenses->select(DB::raw($expense_select));

        // Aplicar filtros comunes
        if ($stablishmentToPayAll !== 1 && $stablishmentToPayAll !== "1") {
            if ($establishment_id && $establishment_id != 0) {
                $purchases->where('purchases.establishment_id', $establishment_id);
                $expenses->where('expenses.establishment_id', $establishment_id);
            }
        }

        if ($d_start && $d_end) {
            $purchases->whereBetween('purchases.date_of_issue', [$d_start, $d_end]);
            $bills_of_exchange->whereBetween('bills_of_exchange_pay.date_of_due', [$d_start, $d_end]);
            $expenses->whereBetween('expenses.date_of_issue', [$d_start, $d_end]);
        }

        if ($supplier_id) {
            $purchases->where('purchases.supplier_id', $supplier_id);
            $bills_of_exchange->where('bills_of_exchange_pay.supplier_id', $supplier_id);
            $expenses->where('expenses.supplier_id', $supplier_id);
        }

        if ($user_id) {
            $purchases->where('purchases.user_id', $user_id);
            $bills_of_exchange->where('bills_of_exchange_pay.user_id', $user_id);
            $expenses->where('expenses.user_id', $user_id);
        }

        // Filtrar solo registros con saldo > 0
        $purchases = $purchases->havingRaw('CAST(total_subtraction AS DECIMAL(12,2)) > 0');
        $bills_of_exchange = $bills_of_exchange->havingRaw('CAST(total_subtraction AS DECIMAL(12,2)) > 0');
        $expenses = $expenses->havingRaw('CAST(total_subtraction AS DECIMAL(12,2)) > 0');
        
        return $purchases->union($bills_of_exchange)
            ->union($expenses);
    }
    

    public static function getUnpaidFilterUser($request)
    {

        $configuration = Configuration::first();
        $show_all =  $configuration->show_all_unpaid;
        $establishment_id = $request['establishment_id'] ?? null;
        $period = $request['period'] ?? null;
        $date_start = $request['date_start'] ?? null;
        $date_end = $request['date_end'] ?? null;
        $ubigeo = Functions::valueKeyInArray($request, 'ubigeo');
        $seller_id = Functions::valueKeyInArray($request, 'seller_id');
        $department_id = null;
        $province_id = null;
        if ($ubigeo) {
            $ubigeo = explode(',', $ubigeo);
            $department_id = $ubigeo[0];
            if (count($ubigeo) > 1) {
                $province_id = $ubigeo[1];
            }
        }
        $month_start = $request['month_start'] ?? null;
        $month_end = $request['month_end'] ?? null;
        $customer_id = $request['customer_id'] ?? null;
        $extern = $request['extern'] ?? null;
        if ($extern == 'true') {
            $establishment_id = auth()->user()->establishment_id;
        }
        $user_id = $request['user_id'] ?? null;
        $web_platform_id = $request['web_platform_id'] ?? 0;
        $purchase_order = $request['purchase_order'] ?? null;
        $zone_id = Functions::valueKeyInArray($request, 'zone_id');
        $document_number = Functions::valueKeyInArray($request, 'document_number');
        $payment_method_type_id = $request['payment_method_type_id'] ?? null;
        // Obtendrá todos los establecimientos
        $stablishmentUnpaidAll = $request['stablishmentUnpaidAll'] ?? 0;
        $user = auth()->user();
        if (null === $user) {
            $user = new \App\Models\Tenant\User();
        }
        $user_type = $user->type;
        $user_id_session = $user->id;
        $d_start = null;
        $d_end = null;
        $with_payments = true;
        /** @todo: Eliminar periodo, fechas y cambiar por

        $date_start = $request['date_start'];
        $date_end = $request['date_end'];
        \App\CoreFacturalo\Helpers\Functions\FunctionsHelper\FunctionsHelper::setDateInPeriod($request, $date_start, $date_end);
         */
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
        /*
         * Documents
         */


        $document_payments = DB::connection('tenant')
            ->table('document_payments')
            ->select(
                'document_id',
                DB::raw('SUM(payment) + COALESCE(
                    (SELECT JSON_UNQUOTE(JSON_EXTRACT(d.retention, "$.amount"))
                    FROM documents d 
                    WHERE d.id = document_payments.document_id 
                    AND d.retention IS NOT NULL), 
                    0
                ) as total_payment')
            )
            ->groupBy('document_id');
        $bills_of_exchanges_payments = DB::connection('tenant')
            ->table('bills_of_exchange_payments')
            ->select('bill_of_exchange_id', DB::raw('SUM(payment) as total_payment'))
            ->groupBy('bill_of_exchange_id');
        $bills_of_exchanges_select = "bills_of_exchange.id as id, " .
            "DATE_FORMAT(bills_of_exchange.date_of_due, '%Y/%m/%d') as date_of_issue, " .
            "persons.name as customer_name," .
            "persons.line_credit as line_credit," .
            "persons.id as customer_id," .
            "null as document_type_id," .
            "CONCAT(bills_of_exchange.series,'-',bills_of_exchange.number) AS number_full, " .
            "bills_of_exchange.total as total, " .
            "IFNULL(payments.total_payment, 0) as total_payment, " .
            "null as total_credit_notes," .
            "bills_of_exchange.total - IFNULL(total_payment, 0)    as total_subtraction, " .
            "'bill_of_exchange' AS 'type', " .
            "null as retention, " .
            "bills_of_exchange.currency_type_id, " .
            "bills_of_exchange.exchange_rate_sale, " .
            " bills_of_exchange.user_id, " .
            "users.name as username";
        $document_select = "documents.id as id, " .
            "DATE_FORMAT(documents.date_of_issue, '%Y/%m/%d') as date_of_issue, " .
            "persons.name as customer_name," .
            "persons.line_credit as line_credit," .
            "persons.id as customer_id," .
            "documents.document_type_id," .
            "CONCAT(documents.series,'-',documents.number) AS number_full, " .
            "documents.total as total, " .
            "IFNULL(payments.total_payment, 0) as total_payment, " .
            "IFNULL(credit_notes.total_credit_notes, 0) as total_credit_notes, " .
            "documents.total - IFNULL(total_payment, 0)  - IFNULL(total_credit_notes, 0)  as total_subtraction, " .
            "'document' AS 'type', " .
            "documents.retention as retention, " .
            "documents.currency_type_id, " .
            "documents.exchange_rate_sale, " .
            " documents.user_id, " .
            "users.name as username";

        $sale_note_select = "sale_notes.id as id, " .
            "DATE_FORMAT(sale_notes.date_of_issue, '%Y/%m/%d') as date_of_issue, " .
            "persons.name as customer_name," .
            "persons.line_credit as line_credit," .
            "persons.id as customer_id," .
            "null as document_type_id," .
            "sale_notes.filename as number_full, " .
            "sale_notes.total as total, " .
            "IFNULL(payments.total_payment, 0) as total_payment, " .
            "null as total_credit_notes," .
            "sale_notes.total - IFNULL(total_payment, 0)  as total_subtraction, " .
            "'sale_note' AS 'type', " .
            "null as retention, " .
            "sale_notes.currency_type_id, " .
            "sale_notes.exchange_rate_sale, " .
            " sale_notes.user_id, " .
            "users.name as username";

        $bills_of_exchange = DB::connection('tenant')
            ->table('bills_of_exchange')
            //->where('customer_id', $customer_id)
            ->join('persons', 'persons.id', '=', 'bills_of_exchange.customer_id')
            ->join('users', 'users.id', '=', 'bills_of_exchange.user_id')
            ->leftJoinSub($bills_of_exchanges_payments, 'payments', function ($join) {
                $join->on('bills_of_exchange.id', '=', 'payments.bill_of_exchange_id');
            });
            
            $bills_of_exchange = $bills_of_exchange->select(DB::raw($bills_of_exchanges_select));

        if($stablishmentUnpaidAll !== 1 && $stablishmentUnpaidAll !== "1"){
            $bills_of_exchange->where('bills_of_exchange.establishment_id', $establishment_id);
        }
        $documents = DB::connection('tenant')
            ->table('documents')
            ->join('persons', 'persons.id', '=', 'documents.customer_id')
            ->join('users', 'users.id', '=', 'documents.user_id')
            ->leftJoinSub($document_payments, 'payments', function ($join) {
                $join->on('documents.id', '=', 'payments.document_id');
            })
            ->leftJoinSub(Document::getQueryCreditNotes(), 'credit_notes', function ($join) {
                $join->on('documents.id', '=', 'credit_notes.affected_document_id');
            })
            ->whereNotExists(function ($query) {
                $query->select(DB::raw('sale_notes.id'))
                    ->from('sale_notes')
                    ->whereRaw('sale_notes.id = documents.sale_note_id')
                    ->where(function ($query) {
                        $query->where('sale_notes.total_canceled', true)
                            ->orWhere('sale_notes.paid', true);
                    });
            })
            ->whereIn('state_type_id', ['01', '03', '05', '07', '13'])
            ->whereIn('document_type_id', ['01', '03', '08']);
            
            $documents = $documents->select(DB::raw($document_select));

        if ($stablishmentUnpaidAll !== 1 && $stablishmentUnpaidAll !== "1") {
            $documents->where('documents.establishment_id', $establishment_id);
        }

        if ($payment_method_type_id) {
            $documents->where('payment_method_type_id', $payment_method_type_id);
        }
        $documents->whereNull('bill_of_exchange_id');

        $sale_note_payments = DB::connection('tenant')
            ->table('sale_note_payments')
            ->select('sale_note_id', DB::raw('SUM(payment) as total_payment'))
            ->groupBy('sale_note_id');

        $sale_notes = DB::connection('tenant')
            ->table('sale_notes')
            ->join('persons', 'persons.id', '=', 'sale_notes.customer_id')
            ->join('users', 'users.id', '=', 'sale_notes.user_id')
            ->leftJoinSub($sale_note_payments, 'payments', function ($join) {
                $join->on('sale_notes.id', '=', 'payments.sale_note_id');
            })
            ->whereIn('state_type_id', ['01', '03', '05', '07', '13'])
            ->select(DB::raw($sale_note_select))
            ->where('sale_notes.changed', false)

            ->where('sale_notes.total_canceled', false);
        

        if ($stablishmentUnpaidAll !== 1 && $stablishmentUnpaidAll !== "1") {
            $sale_notes->where('sale_notes.establishment_id', $establishment_id);
        }

        if (!$show_all) {
            if ($user_type == 'seller') { // Línea modificada
                $sale_notes->where('user_id', $user_id_session);
                $documents->where('user_id', $user_id_session);
            }
        }

        if ($user_id) {
            $sale_notes->where('user_id', $user_id);
            $documents->where('user_id', $user_id);
            $bills_of_exchange->where('user_id', $user_id);
        }
        if ($customer_id) {
            $sale_notes->where('customer_id', $customer_id);
            $documents->where('customer_id', $customer_id);
            $bills_of_exchange->where('customer_id', $customer_id);
        }
        if ($zone_id) {
            $sale_notes->where('persons.zone_id', $zone_id);
            $documents->where('persons.zone_id', $zone_id);
            $bills_of_exchange->where('persons.zone_id', $zone_id);
        }
        if ($department_id) {
            if ($province_id) {
                $sale_notes->where('persons.province_id', $province_id);
                $documents->where('persons.province_id', $province_id);
                $bills_of_exchange->where('persons.province_id', $province_id);
            } else {
                $sale_notes->where('persons.department_id', $department_id);
                $documents->where('persons.department_id', $department_id);
                $bills_of_exchange->where('persons.department_id', $department_id);
            }
        }
        if ($seller_id) {
            $sale_notes->where('sale_notes.seller_id', $seller_id);
            $documents->where('documents.seller_id', $seller_id);
            $bills_of_exchange->where('bills_of_exchange.user_id', $seller_id);
        }
        if ($d_start && $d_end) {
            $sale_notes->whereBetween('sale_notes.date_of_issue', [$d_start, $d_end]);
            $documents->whereBetween('documents.date_of_issue', [$d_start, $d_end]);
            $bills_of_exchange->whereBetween('bills_of_exchange.created_at', [$d_start, $d_end]);
        }
        if ($purchase_order !== null) {
            $documents->where('purchase_order', $purchase_order);
            $sale_notes->where('purchase_order', $purchase_order);
        }
        if ($web_platform_id != 0) {
            $web_platform_table_name = (new WebPlatform())->getTable();
            $item_table_name = (new Item())->getTable();
            $document_item_table = (new DocumentItem())->getTable();
            $sale_note_item_table = (new SaleNoteItem())->getTable();
            $document_items_id = DocumentItem::leftJoin($item_table_name, "$item_table_name.id", '=', "$document_item_table.item_id")
                ->leftJoin($web_platform_table_name, "$web_platform_table_name.id", '=', "$item_table_name.web_platform_id")
                ->where("$item_table_name.web_platform_id", $web_platform_id)
                ->select($document_item_table . '.document_id as document_id')
                ->get()
                ->pluck('document_id');
            $documents->wherein('documents.id', $document_items_id);

            $sale_note_items_id = SaleNoteItem::leftJoin($item_table_name, "$item_table_name.id", '=', "$sale_note_item_table.item_id")
                ->leftJoin($web_platform_table_name, "$web_platform_table_name.id", '=', "$item_table_name.web_platform_id")
                ->where("$item_table_name.web_platform_id", $web_platform_id)
                ->select($sale_note_item_table . '.sale_note_id as document_id')
                ->get()
                ->pluck('document_id');
            $sale_notes->wherein('sale_notes.id', $sale_note_items_id);
        }

        if ($document_number) {
            $documents->where('documents.number', 'like', '%' . $document_number . '%');
            $sale_notes->where('sale_notes.number', 'like', '%' . $document_number . '%');
            $bills_of_exchange->where('bills_of_exchange.number', 'like', '%' . $document_number . '%');
        }

    

        $documents = $documents->havingRaw('CAST(total_subtraction AS DECIMAL(12,2)) > 0');
        $sale_notes = $sale_notes->havingRaw('CAST(total_subtraction AS DECIMAL(12,2)) > 0');
        $bills_of_exchange = $bills_of_exchange->havingRaw('CAST(total_subtraction AS DECIMAL(12,2)) > 0');

        return $documents->union($sale_notes)
            ->union($bills_of_exchange);
    }

    public static function getUnpaidByCustomer($customer_id)
    {
        $configuration = Configuration::first();
        $show_all =  $configuration->show_all_unpaid;

        $user = auth()->user();
        if (null === $user) {
            $user = new \App\Models\Tenant\User();
        }
        $user_type = $user->type;
        $user_id_session = $user->id;




        $document_payments = DB::connection('tenant')
            ->table('document_payments')
            ->select(
                'document_id',
                DB::raw('SUM(payment) + COALESCE(
                    (SELECT JSON_UNQUOTE(JSON_EXTRACT(d.retention, "$.amount"))
                    FROM documents d 
                    WHERE d.id = document_payments.document_id 
                    AND d.retention IS NOT NULL), 
                    0
                ) as total_payment')
            )
            ->groupBy('document_id');
        $bills_of_exchanges_payments = DB::connection('tenant')
            ->table('bills_of_exchange_payments')
            ->select('bill_of_exchange_id', DB::raw('SUM(payment) as total_payment'))
            ->groupBy('bill_of_exchange_id');
        $bills_of_exchanges_select = "bills_of_exchange.id as id, " .
            "DATE_FORMAT(bills_of_exchange.date_of_due, '%Y/%m/%d') as date_of_issue, " .
            "persons.name as customer_name," .
            "persons.id as customer_id," .
            "null as document_type_id," .
            "CONCAT(bills_of_exchange.series,'-',bills_of_exchange.number) AS number_full, " .
            "bills_of_exchange.total as total, " .
            "IFNULL(payments.total_payment, 0) as total_payment, " .
            "null as total_credit_notes," .
            "bills_of_exchange.total - IFNULL(total_payment, 0)    as total_subtraction, " .
            "'bill_of_exchange' AS 'type', " .
            "null as retention, " .
            "bills_of_exchange.currency_type_id, " .
            "bills_of_exchange.exchange_rate_sale, " .
            " bills_of_exchange.user_id, " .
            "users.name as username";
        $document_select = "documents.id as id, " .
            "DATE_FORMAT(documents.date_of_issue, '%Y/%m/%d') as date_of_issue, " .
            "persons.name as customer_name," .
            "persons.id as customer_id," .
            "documents.document_type_id," .
            "CONCAT(documents.series,'-',documents.number) AS number_full, " .
            "documents.total as total, " .
            "IFNULL(payments.total_payment, 0) as total_payment, " .
            "IFNULL(credit_notes.total_credit_notes, 0) as total_credit_notes, " .
            "documents.total - IFNULL(total_payment, 0)  - IFNULL(total_credit_notes, 0)  as total_subtraction, " .
            "'document' AS 'type', " .
            "documents.retention as retention, " .
            "documents.currency_type_id, " .
            "documents.exchange_rate_sale, " .
            " documents.user_id, " .
            "users.name as username";

        $sale_note_select = "sale_notes.id as id, " .
            "DATE_FORMAT(sale_notes.date_of_issue, '%Y/%m/%d') as date_of_issue, " .
            "persons.name as customer_name," .
            "persons.id as customer_id," .
            "null as document_type_id," .
            "sale_notes.filename as number_full, " .
            "sale_notes.total as total, " .
            "IFNULL(payments.total_payment, 0) as total_payment, " .
            "null as total_credit_notes," .
            "sale_notes.total - IFNULL(total_payment, 0)  as total_subtraction, " .
            "'sale_note' AS 'type', " .
            "null as retention, " .
            "sale_notes.currency_type_id, " .
            "sale_notes.exchange_rate_sale, " .
            " sale_notes.user_id, " .
            "users.name as username";

        $bills_of_exchange = DB::connection('tenant')
            ->table('bills_of_exchange')
            //->where('customer_id', $customer_id)
            ->join('persons', 'persons.id', '=', 'bills_of_exchange.customer_id')
            ->join('users', 'users.id', '=', 'bills_of_exchange.user_id')
            ->leftJoinSub($bills_of_exchanges_payments, 'payments', function ($join) {
                $join->on('bills_of_exchange.id', '=', 'payments.bill_of_exchange_id');
            })

            ->select(DB::raw($bills_of_exchanges_select));
        $documents = DB::connection('tenant')
            ->table('documents')
            ->join('persons', 'persons.id', '=', 'documents.customer_id')
            ->join('users', 'users.id', '=', 'documents.user_id')
            ->leftJoinSub($document_payments, 'payments', function ($join) {
                $join->on('documents.id', '=', 'payments.document_id');
            })
            ->leftJoinSub(Document::getQueryCreditNotes(), 'credit_notes', function ($join) {
                $join->on('documents.id', '=', 'credit_notes.affected_document_id');
            })
            ->whereNotExists(function ($query) {
                $query->select(DB::raw('sale_notes.id'))
                    ->from('sale_notes')
                    ->whereRaw('sale_notes.id = documents.sale_note_id')
                    ->where(function ($query) {
                        $query->where('sale_notes.total_canceled', true)
                            ->orWhere('sale_notes.paid', true);
                    });
            })
            ->whereIn('state_type_id', ['01', '03', '05', '07', '13'])
            ->whereIn('document_type_id', ['01', '03', '08'])
            ->where('payment_condition_id', '01')
            ->select(DB::raw($document_select));

        $documents->whereNull('bill_of_exchange_id');

        $sale_note_payments = DB::connection('tenant')
            ->table('sale_note_payments')
            ->select('sale_note_id', DB::raw('SUM(payment) as total_payment'))
            ->groupBy('sale_note_id');

        $sale_notes = DB::connection('tenant')
            ->table('sale_notes')
            ->join('persons', 'persons.id', '=', 'sale_notes.customer_id')
            ->join('users', 'users.id', '=', 'sale_notes.user_id')
            ->leftJoinSub($sale_note_payments, 'payments', function ($join) {
                $join->on('sale_notes.id', '=', 'payments.sale_note_id');
            })
            ->whereIn('state_type_id', ['01', '03', '05', '07', '13'])
            ->select(DB::raw($sale_note_select))
            ->where('sale_notes.changed', false)
            ->where('sale_notes.total_canceled', false);

        if (!$show_all) {
            if ($user_type == 'seller') { // Línea modificada
                $sale_notes->where('user_id', $user_id_session);
                $documents->where('user_id', $user_id_session);
            }
        }

        if ($customer_id) {
            $sale_notes->where('customer_id', $customer_id);
            $documents->where('customer_id', $customer_id);
            $bills_of_exchange->where('customer_id', $customer_id);
        }

        $documents = $documents->havingRaw('CAST(total_subtraction AS DECIMAL(12,2)) > 0');
        $sale_notes = $sale_notes->havingRaw('CAST(total_subtraction AS DECIMAL(12,2)) > 0');
        $bills_of_exchange = $bills_of_exchange->havingRaw('CAST(total_subtraction AS DECIMAL(12,2)) > 0');

        // Agregamos la consulta para documentos a crédito y sus cuotas
        $document_fees_select = "documents.id as id, " .
            "DATE_FORMAT(document_fee.date, '%Y/%m/%d') as date_of_issue, " .
            "persons.name as customer_name," .
            "persons.id as customer_id," .
            "documents.document_type_id," .
            "CONCAT(documents.series,'-',documents.number) AS number_full, " .
            "IFNULL(document_fee.original_amount, document_fee.amount) as total, " .
            "CASE WHEN document_fee.is_canceled = 1 THEN document_fee.original_amount ELSE (document_fee.original_amount - document_fee.amount) END as total_payment, " .
            "0 as total_credit_notes, " .
            "CASE WHEN document_fee.is_canceled = 1 THEN 0 ELSE document_fee.amount END as total_subtraction, " .
            "'document_fee' AS 'type', " .
            "documents.retention as retention, " .
            "documents.currency_type_id, " .
            "documents.exchange_rate_sale, " .
            "documents.user_id, " .
            "users.name as username";

        $documents_fees = DB::connection('tenant')
            ->table('documents')
            ->join('persons', 'persons.id', '=', 'documents.customer_id')
            ->join('users', 'users.id', '=', 'documents.user_id')
            ->join('document_fee', 'documents.id', '=', 'document_fee.document_id')
            ->whereIn('documents.state_type_id', ['01', '03', '05', '07', '13'])
            ->whereIn('documents.document_type_id', ['01', '03', '08'])
            ->where('documents.payment_condition_id', '02')
            ->where('document_fee.is_canceled', false)
            ->select(DB::raw($document_fees_select));

        if (!$show_all) {
            if ($user_type == 'seller') {
                $documents_fees->where('user_id', $user_id_session);
            }
        }

        if ($customer_id) {
            $documents_fees->where('customer_id', $customer_id);
        }


        $documents_fees = $documents_fees->havingRaw('CAST(total_subtraction AS DECIMAL(12,2)) > 0');
        return $documents->union($documents_fees)
            ->union($sale_notes)
            ->union($bills_of_exchange);
    }

    public static function getUnpaidByCustomerJustTotalAndTotalPayment($customer_id)
    {
        $configuration = Configuration::getConfig();
        $show_all =  $configuration->show_all_unpaid;

        $user = auth()->user();
        if (null === $user) {
            $user = new \App\Models\Tenant\User();
        }
        $user_type = $user->type;
        $user_id_session = $user->id;

        $document_payments = DB::connection('tenant')
            ->table('document_payments')
            ->select(
                'document_id',
                DB::raw('SUM(payment) + COALESCE(
                    (SELECT JSON_UNQUOTE(JSON_EXTRACT(d.retention, "$.amount"))
                    FROM documents d 
                    WHERE d.id = document_payments.document_id 
                    AND d.retention IS NOT NULL), 
                    0
                ) as total_payment')
            )
            ->groupBy('document_id');
            
        $bills_of_exchanges_payments = DB::connection('tenant')
            ->table('bills_of_exchange_payments')
            ->select('bill_of_exchange_id', DB::raw('SUM(payment) as total_payment'))
            ->groupBy('bill_of_exchange_id');
            
        $bills_of_exchanges_select = "bills_of_exchange.id as id, bills_of_exchange.total as total, " .
            "IFNULL(payments.total_payment, 0) as total_payment";
            
        $document_select = "documents.id as id, documents.total as total, " .
            "IFNULL(payments.total_payment, 0) as total_payment";

        $sale_note_select = "sale_notes.id as id, sale_notes.total as total, " .
            "IFNULL(payments.total_payment, 0) as total_payment";

        $bills_of_exchange = DB::connection('tenant')
            ->table('bills_of_exchange')
            ->join('persons', 'persons.id', '=', 'bills_of_exchange.customer_id')
            ->join('users', 'users.id', '=', 'bills_of_exchange.user_id')
            ->leftJoinSub($bills_of_exchanges_payments, 'payments', function ($join) {
                $join->on('bills_of_exchange.id', '=', 'payments.bill_of_exchange_id');
            })
            ->select(DB::raw($bills_of_exchanges_select));
            
        $documents = DB::connection('tenant')
            ->table('documents')
            ->join('persons', 'persons.id', '=', 'documents.customer_id')
            ->join('users', 'users.id', '=', 'documents.user_id')
            ->leftJoinSub($document_payments, 'payments', function ($join) {
                $join->on('documents.id', '=', 'payments.document_id');
            })
            ->leftJoinSub(Document::getQueryCreditNotes(), 'credit_notes', function ($join) {
                $join->on('documents.id', '=', 'credit_notes.affected_document_id');
            })
            ->whereNotExists(function ($query) {
                $query->select(DB::raw('sale_notes.id'))
                    ->from('sale_notes')
                    ->whereRaw('sale_notes.id = documents.sale_note_id')
                    ->where(function ($query) {
                        $query->where('sale_notes.total_canceled', true)
                            ->orWhere('sale_notes.paid', true);
                    });
            })
            ->whereIn('state_type_id', ['01', '03', '05', '07', '13'])
            ->whereIn('document_type_id', ['01', '03', '08'])
            ->where('payment_condition_id', '01')
            ->select(DB::raw($document_select));

        $documents->whereNull('bill_of_exchange_id');

        $sale_note_payments = DB::connection('tenant')
            ->table('sale_note_payments')
            ->select('sale_note_id', DB::raw('SUM(payment) as total_payment'))
            ->groupBy('sale_note_id');

        $sale_notes = DB::connection('tenant')
            ->table('sale_notes')
            ->join('persons', 'persons.id', '=', 'sale_notes.customer_id')
            ->join('users', 'users.id', '=', 'sale_notes.user_id')
            ->leftJoinSub($sale_note_payments, 'payments', function ($join) {
                $join->on('sale_notes.id', '=', 'payments.sale_note_id');
            })
            ->whereIn('state_type_id', ['01', '03', '05', '07', '13'])
            ->select(DB::raw($sale_note_select))
            ->where('sale_notes.changed', false)
            ->where('sale_notes.total_canceled', false);

        if (!$show_all) {
            if ($user_type == 'seller') {
                $sale_notes->where('user_id', $user_id_session);
                $documents->where('user_id', $user_id_session);
            }
        }

        if ($customer_id) {
            $sale_notes->where('customer_id', $customer_id);
            $documents->where('customer_id', $customer_id);
            $bills_of_exchange->where('customer_id', $customer_id);
        }

        $document_fees_select = "document_fee.id as id, " .
            "IFNULL(document_fee.original_amount, document_fee.amount) as total, " .
            "CASE WHEN document_fee.is_canceled = 1 THEN document_fee.original_amount ELSE (document_fee.original_amount - document_fee.amount) END as total_payment";

        $documents_fees = DB::connection('tenant')
            ->table('documents')
            ->join('persons', 'persons.id', '=', 'documents.customer_id')
            ->join('users', 'users.id', '=', 'documents.user_id')
            ->join('document_fee', 'documents.id', '=', 'document_fee.document_id')
            ->whereIn('state_type_id', ['01', '03', '05', '07', '13'])
            ->whereIn('document_type_id', ['01', '03', '08'])
            ->whereNotExists(function ($query) {
                $query->select(DB::raw('1'))
                      ->from('notes as note2')
                      ->whereRaw('note2.affected_document_id = documents.id');
            })
            ->where('payment_condition_id', '02')
            ->where('document_fee.is_canceled', false)
            ->select(DB::raw($document_fees_select));

        if (!$show_all) {
            if ($user_type == 'seller') {
                $documents_fees->where('user_id', $user_id_session);
            }
        }

        if ($customer_id) {
            $documents_fees->where('customer_id', $customer_id);
        }

        // Crear subconsultas para aplicar el filtro de monto pendiente
        $documents_with_pending = DB::connection('tenant')
            ->table(DB::raw("({$documents->toSql()}) as documents_sub"))
            ->mergeBindings($documents)
            ->whereRaw('(total - IFNULL(total_payment, 0)) > 0');

        $sale_notes_with_pending = DB::connection('tenant')
            ->table(DB::raw("({$sale_notes->toSql()}) as sale_notes_sub"))
            ->mergeBindings($sale_notes)
            ->whereRaw('(total - IFNULL(total_payment, 0)) > 0');

        $bills_of_exchange_with_pending = DB::connection('tenant')
            ->table(DB::raw("({$bills_of_exchange->toSql()}) as bills_of_exchange_sub"))
            ->mergeBindings($bills_of_exchange)
            ->whereRaw('(total - IFNULL(total_payment, 0)) > 0');

        $documents_fees_with_pending = DB::connection('tenant')
            ->table(DB::raw("({$documents_fees->toSql()}) as documents_fees_sub"))
            ->mergeBindings($documents_fees)
            ->whereRaw('(total - IFNULL(total_payment, 0)) > 0');

        return $documents_with_pending->union($documents_fees_with_pending)
            ->union($sale_notes_with_pending)
            ->union($bills_of_exchange_with_pending);
    }
}

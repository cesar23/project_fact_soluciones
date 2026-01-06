<?php

namespace Modules\Finance\Traits;

use App\Models\Tenant\BillOfExchange;
use App\Models\Tenant\BillOfExchangePayment;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\Document;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Dispatch;
use App\Models\Tenant\DocumentFee;
use App\Models\Tenant\DocumentItem;
use App\Models\Tenant\DocumentPayment;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\SaleNotePayment;
use App\Models\Tenant\Invoice;
use App\Models\Tenant\Note;
use App\Models\Tenant\Person;
use App\Models\Tenant\SaleNoteItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

trait UnpaidTrait
{

    public function transformRecords3($records, $detail = null)
    {
        // Pre-cargar relaciones para evitar consultas N+1
        $records->load([
            'user',
            'currency_type'
        ]);

        // Pre-cargar invoices para documentos
        $documentIds = $records->where('document_type_id', '!=', null)->pluck('id')->toArray();
        $invoices = collect();
        if (!empty($documentIds)) {
            $invoices = Invoice::whereIn('document_id', $documentIds)->get()->keyBy('document_id');
        }

        // Pre-cargar últimos pagos para documentos
        $lastDocumentPayments = collect();
        if (!empty($documentIds)) {
            $lastDocumentPayments = DocumentPayment::whereIn('document_id', $documentIds)
                ->select('document_id', 'date_of_payment')
                ->orderBy('date_of_payment', 'desc')
                ->get()
                ->groupBy('document_id')
                ->map(function ($payments) {
                    return $payments->first();
                });
        }

        // Pre-cargar últimos pagos para sale notes
        $saleNoteIds = $records->where('type', 'sale_note')->pluck('id')->toArray();
        $lastSaleNotePayments = collect();
        if (!empty($saleNoteIds)) {
            $lastSaleNotePayments = SaleNotePayment::whereIn('sale_note_id', $saleNoteIds)
                ->select('sale_note_id', 'date_of_payment')
                ->orderBy('date_of_payment', 'desc')
                ->get()
                ->groupBy('sale_note_id')
                ->map(function ($payments) {
                    return $payments->first();
                });
        }

        // Pre-cargar documentos relacionados
        $documentIds = $records->where('type', 'document')->pluck('id')->toArray();
        $documents = collect();
        if (!empty($documentIds)) {
            $documents = Document::whereIn('id', $documentIds)
                ->with(['customer', 'user'])
                ->get()
                ->keyBy('id');
        }

        // Pre-cargar sale notes relacionadas
        $saleNotes = collect();
        if (!empty($saleNoteIds)) {
            $saleNotes = SaleNote::whereIn('id', $saleNoteIds)
                ->with(['customer', 'user'])
                ->get()
                ->keyBy('id');
        }

        // Pre-cargar bill of exchanges
        $billOfExchangeIds = $records->where('type', 'bill_of_exchange')->pluck('id')->toArray();
        $billOfExchanges = collect();
        if (!empty($billOfExchangeIds)) {
            $billOfExchanges = BillOfExchange::whereIn('id', $billOfExchangeIds)->get()->keyBy('id');
        }

        // Pre-cargar document fees
        $documentFeeIds = $records->where('type', 'document_fee')->pluck('id')->toArray();
        $documentFees = collect();
        if (!empty($documentFeeIds)) {
            $documentFees = DocumentFee::whereIn('id', $documentFeeIds)
                ->with('document.customer')
                ->get()
                ->keyBy('id');
        }

        // Pre-cargar zonas de clientes
        $customerIds = $records->pluck('customer_id')->unique()->filter()->toArray();
        $customerZones = collect();
        if (!empty($customerIds)) {
            $customerZones = Person::whereIn('id', $customerIds)
                ->get()
                ->keyBy('id')
                ->map(function ($person) {
                    return $person->getZone();
                });
        }

        $now = Carbon::now();

        return $records->transform(function ($row, $key) use (
            $detail, 
            $invoices, 
            $lastDocumentPayments, 
            $lastSaleNotePayments,
            $documents,
            $saleNotes,
            $billOfExchanges,
            $documentFees,
            $customerZones,
            $now
        ) {
            $total_to_pay = $this->getTotalToPay($row);
            $date_of_issue = $row->date_of_issue;
            $delay_payment = null;
            $date_of_due = null;

            // Verificar invoice para documentos
            if ($total_to_pay > 0 && $row->document_type_id) {
                $invoice = $invoices->get($row->id);
                if ($invoice) {
                    $due = Carbon::parse($invoice->date_of_due);
                    $date_of_due = $invoice->date_of_due->format('Y/m/d');
                    if ($now > $due) {
                        $delay_payment = $now->diffInDays($due);
                    }
                }
            }

            $date_payment_last = null;
            $payments = [];
            $number_full = $row->number_full;
            
            if ($detail) {
                $payments = $this->getPayments($row);
            }

            // Obtener último pago
            if ($row->document_type_id) {
                $date_payment_last = $lastDocumentPayments->get($row->id);
            } else {
                $date_payment_last = $lastSaleNotePayments->get($row->id);
            }

            $customer_trade_name = null;
            $customer_zone_name = null;
            $customer_address = null;
            $customer_email = null;
            $seller_name = null;
            $document_related = null;
            $document = null;

            // Procesar según el tipo
            if ($row->type == 'document') {
                $document = $documents->get($row->id);
            } elseif ($row->type == 'sale_note') {
                $document = $saleNotes->get($row->id);
                if ($document) {
                    if ($document->due_date) {
                        $date_of_due = $document->due_date->format('Y/m/d');
                    } else {
                        $date_of_due = $document->date_of_issue->format('Y/m/d');
                    }
                    if ($now > Carbon::parse($date_of_due)) {
                        $delay_payment = $now->diffInDays(Carbon::parse($date_of_due));
                    }
                }
            } elseif ($row->type == 'bill_of_exchange') {
                $billOfExchange = $billOfExchanges->get($row->id);
                if ($billOfExchange) {
                    $number_full = $billOfExchange->number_full;
                    $date_of_due = is_null($billOfExchange->date_of_due) ? null : $billOfExchange->date_of_due->format('Y/m/d');
                    $date_of_issue = $billOfExchange->created_at->format('Y/m/d');
                }
            } elseif ($row->type == 'document_fee') {
                $documentFee = $documentFees->get($row->id);
                if ($documentFee) {
                    $document = $documentFee->document;
                    $number_full = $document->number_full;
                    $date_of_due = is_null($document->date_of_due) ? null : $document->date_of_due->format('Y/m/d');
                    $date_of_issue = $document->created_at->format('Y/m/d');
                }
            }

            // Procesar información del cliente y vendedor
            if ($document) {
                $customer_trade_name = $document->customer->trade_name ?? null;
                $customer_email = $document->customer->email ?? null;
                $customer_address = $document->customer->address ?? null;
                $customer_id = $document->customer_id;
                $seller_name = $document->user->name ?? null;
                
                $zone = $customerZones->get($customer_id);
                if ($zone) {
                    $customer_zone_name = $zone->name;
                }
                
                $document_related = $document->series . '-' . $document->number;
            }

            $currency_symbol = $row->currency_type->symbol ?? '';

            return [
                'document_related' => $document_related,
                'currency_symbol' => $currency_symbol,
                'id' => $row->id,
                'seller_name' => $seller_name,
                'date_of_issue' => $date_of_issue,
                'customer_name' => $row->customer_name,
                'customer_address' => $customer_address,
                'customer_email' => $customer_email,
                'customer_zone_name' => $customer_zone_name,
                'customer_trade_name' => $customer_trade_name,
                'customer_id' => $row->customer_id,
                'number_full' => $number_full,
                'total' => number_format((float) $row->total, 2, ".", ""),
                'total_to_pay' => number_format($total_to_pay, 2, ".", ""),
                'type' => $row->type,
                'payments' => $payments,
                'date_payment_last' => ($date_payment_last) ? $date_payment_last->date_of_payment->format('Y-m-d') : null,
                'delay_payment' => $delay_payment,
                'date_of_due' =>  $date_of_due,
                'currency_type_id' => $row->currency_type_id,
                'exchange_rate_sale' => (float)$row->exchange_rate_sale,
                "user_id" => $row->user_id,
                "username" => $row->username,
                "total_subtraction" => $row->total_subtraction,
                "total_payment" => $row->total_payment,
            ];
        });
    }
    public function transformRecords($records, $detail = null)
    {

        return $records->transform(function ($row, $key)  use ($detail) {
            $total_to_pay = $this->getTotalToPay($row);
            $date_of_issue = $row->date_of_issue;
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
            $payments = [];
            $number_full = $row->number_full;
            if ($detail) {
                $payments = $this->getPayments($row);
            }
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
            $purchase_order = null;
            if ($row->type == 'document') {
                $document = Document::find($row->id);
                if($document->payment_condition_id == '02'){
                    $first_fee_not_canceled = DocumentFee::where('document_id', $row->id)->where('is_canceled', false)->first();
                    if($first_fee_not_canceled){
                        $delay_payment = null;
                        $date_of_due = $first_fee_not_canceled->date->format('Y/m/d');
                        $now = Carbon::now();
                        if ($now > $first_fee_not_canceled->date) {
                            $delay_payment = $now->diffInDays($date_of_due);
                        }
                    }
                }
                $web_platforms = $document->getPlatformThroughItems();
                $purchase_order = $document->purchase_order;
            } elseif ($row->type == 'sale_note') {
                $date_of_due = null;
                $document = SaleNote::find($row->id);
                $web_platforms = $document->getPlatformThroughItems();
                $purchase_order = $document->purchase_order;
                $now = Carbon::now();
                if ($document->due_date) {
                    $date_of_due = $document->due_date->format('Y/m/d');
                } else {
                    $date_of_due =  $document->date_of_issue->format('Y/m/d');
                }
                if ($now > Carbon::parse($date_of_due)) {
                    $delay_payment = $now->diffInDays(Carbon::parse($date_of_due));
                }
            } elseif ($row->type == 'bill_of_exchange') {
                $date_of_due = null;
                $document = BillOfExchange::find($row->id);
                $web_platforms = [];
                $purchase_order = null;
                $number_full = $document->number_full;
                $date_of_due = is_null($document->date_of_due) ? null : $document->date_of_due->format('Y/m/d');
                $now = Carbon::now();
                if ($now > $document->date_of_due) {
                    $delay_payment = $now->diffInDays($date_of_due);
                }
                $date_of_issue = $document->created_at->format('Y/m/d');
            }
            elseif ($row->type == 'document_fee') {
                $document_fee = DocumentFee::find($row->id);
                $document = $document_fee->document;
                
                $number_full = $document->number_full;
                $date_of_due = is_null($document_fee->date) ? null : $document_fee->date->format('Y/m/d');
                $now = Carbon::now();
                if ($now > $document_fee->date) {
                    $delay_payment = $now->diffInDays($date_of_due);
                }
                $date_of_issue = $document->created_at->format('Y/m/d');
            }
            else {
                $web_platforms = new \Illuminate\Database\Eloquent\Collection();
            }
            $customer_internal_code = null;
            $customer_trade_name  = null;
            $customer_zone_name = null;
            $customer_telephone = null;
            $customer_address = null;
            $customer_email = null;
            $seller_name = null;
            if ($document) {
                $customer_internal_code = $document->customer->internal_code;
                $customer_trade_name  = $document->customer->trade_name;
                $customer_telephone = $document->customer->telephone;
                $customer_email = $document->customer->email;
                $customer_address = $document->customer->address;
                $customer_id = $document->customer_id;
                $zone = Person::find($customer_id)->getZone();
                $seller_name = $document->user->name;
                if ($zone) {
                    $customer_zone_name = $zone->name;
                }
            }
            $currency_type_id = $row->currency_type_id;
            $currency = CurrencyType::find($currency_type_id);
            $currency_symbol = ($currency) ? $currency->symbol : '';
            return [
                'currency_symbol' => $currency_symbol,
                'id' => $row->id,
                'seller_name' => $seller_name,
                'date_of_issue' => $date_of_issue,
                'customer_name' => $row->customer_name,
                'customer_internal_code' => $customer_internal_code,
                'customer_telephone' => $customer_telephone,
                'customer_address' => $customer_address,
                'customer_email' => $customer_email,
                'customer_zone_name' => $customer_zone_name,
                'customer_trade_name' => $customer_trade_name,
                'customer_id' => $row->customer_id,
                'number_full' => $number_full,
                'total' => number_format((float) $row->total, 2, ".", ""),
                'total_to_pay' => number_format($total_to_pay, 2, ".", ""),
                'type' => $row->type,
                'guides' => $guides,
                'payments' => $payments,
                'date_payment_last' => ($date_payment_last) ? $date_payment_last->date_of_payment->format('Y-m-d') : null,
                'delay_payment' => $delay_payment,
                'date_of_due' =>  $date_of_due,
                'currency_type_id' => $row->currency_type_id,
                'exchange_rate_sale' => (float)$row->exchange_rate_sale,
                "user_id" => $row->user_id,
                "username" => $row->username,
                "total_subtraction" => $row->total_subtraction,
                "total_payment" => $row->total_payment,
                "purchase_order" => $purchase_order,
                "web_platforms" => $web_platforms,
                "total_credit_notes" => $this->getTotalCreditNote($row),
            ];
        });
    }

    public function transformRecords2($records)
    {

        return $records->transform(function ($row, $key)  {
            $total_to_pay = $this->getTotalToPay($row);
            $date_of_issue = $row->date_of_issue;
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

            $number_full = $row->number_full;

            $plate_number = null;
            if ($row->type == 'document') {
                $document = Document::find($row->id);
                $plate_number = $document->plate_number;
            } elseif ($row->type == 'sale_note') {
                $document = SaleNote::find($row->id);
                $plate_number = $document->plate_number;
                $now = Carbon::now();
                if ($document->due_date) {
                    $date_of_due = $document->due_date->format('Y/m/d');
                } else {
                    $date_of_due =  $document->date_of_issue->format('Y/m/d');
                }
                if ($now > Carbon::parse($date_of_due)) {
                    $delay_payment = $now->diffInDays(Carbon::parse($date_of_due));
                }
                if (substr_count($number_full, '-') === 2) {
                    $parts = explode('-', $number_full);
                    $number_full = $parts[0] . '-' . $parts[1];
                }
            }
            $customer_internal_code = null;
            $customer_trade_name  = null;
            $customer_zone_name = null;
            $customer_telephone = null;
            $customer_address = null;
            $customer_email = null;
            $seller_name = null;
            
            if ($document) {
                $customer_internal_code = $document->customer->internal_code;
                $customer_trade_name  = $document->customer->trade_name;
                $customer_telephone = $document->customer->telephone;
                $customer_email = $document->customer->email;
                $customer_address = $document->customer->address;
                $customer_id = $document->customer_id;
                $zone = Person::find($customer_id)->getZone();
                $seller_name = $document->user->name;
                if ($zone) {
                    $customer_zone_name = $zone->name;
                }
            }
            $currency_type_id = $row->currency_type_id;
            $currency = CurrencyType::find($currency_type_id);
            $currency_symbol = ($currency) ? $currency->symbol : '';

            $quantity_total_items = 0;
            if ($row->type == 'document') {
                $quantity_total_items = DocumentItem::where('document_id', $row->id)->sum('quantity');
            } else {
                $quantity_total_items = SaleNoteItem::where('sale_note_id', $row->id)->sum('quantity');
            }
            return [
                'plate_number' => $plate_number,
                'quantity_total_items' => $quantity_total_items,
                'currency_symbol' => $currency_symbol,
                'id' => $row->id,
                'seller_name' => $seller_name,
                'date_of_issue' => $date_of_issue,
                'customer_name' => $row->customer_name,
                'customer_internal_code' => $customer_internal_code,
                'customer_telephone' => $customer_telephone,
                'customer_address' => $customer_address,
                'customer_email' => $customer_email,
                'customer_zone_name' => $customer_zone_name,
                'customer_trade_name' => $customer_trade_name,
                'customer_id' => $row->customer_id,
                'number_full' => $number_full,
                'total' => number_format((float) $row->total, 2, ".", ""),
                'total_to_pay' => number_format($total_to_pay, 2, ".", ""),
                'type' => $row->type,
                'delay_payment' => $delay_payment,
                'date_of_due' =>  $date_of_due,
                'currency_type_id' => $row->currency_type_id,
                'exchange_rate_sale' => (float)$row->exchange_rate_sale,
                "user_id" => $row->user_id,
                "username" => $row->username,
                "total_subtraction" => $row->total_subtraction,
                "total_payment" => $row->total_payment,
            ];
        });
    }
    public function transformRecords_especial($records, $detail = null)
    {

        return $records->transform(function ($row, $key)  use ($detail) {
            $total_to_pay = $this->getTotalToPay($row);
            $date_of_issue = $row->date_of_issue;
            // $total_to_pay = (float)$row->total - (float)$row->total_payment;
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
                        } else {
                            $delay_payment = 0;
                        }
                    }
                    $last_fee = DocumentFee::where('document_id', $row->id)->orderBy('date', 'desc')->first();

                    if ($last_fee) {
                        $now = Carbon::now();
                        $due = Carbon::parse($last_fee->date);
                        $date_of_due = $last_fee->date->format('Y/m/d');

                        if ($now > $due) {

                            $delay_payment = $now->diffInDays($due);
                        } else {
                            $delay_payment = 0;
                        }
                    }
                }
            }

            $guides = null;
            $date_payment_last = '';
            $payments = [];
            $number_full = $row->number_full;
            if ($detail) {
                $payments = $this->getPayments($row);
            }
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
            $purchase_order = null;
            if ($row->type == 'document') {
                $document = Document::find($row->id);
                $web_platforms = $document->getPlatformThroughItems();
                $purchase_order = $document->purchase_order;
            } elseif ($row->type == 'sale_note') {
                $document = SaleNote::find($row->id);
                $web_platforms = $document->getPlatformThroughItems();
                $purchase_order = $document->purchase_order;
                $now = Carbon::now();
                if ($document->due_date) {
                    $date_of_due = $document->due_date->format('Y/m/d');
                } else {
                    $date_of_due =  $document->date_of_issue->format('Y/m/d');
                }
                if ($now > Carbon::parse($date_of_due)) {
                    $delay_payment = $now->diffInDays(Carbon::parse($date_of_due));
                }
            } elseif ($row->type == 'bill_of_exchange') {
                $document = BillOfExchange::find($row->id);
                $web_platforms = [];
                $purchase_order = null;
                $number_full = $document->number_full;
                $date_of_due = is_null($document->date_of_due) ? null : $document->date_of_due->format('Y/m/d');
                $date_of_issue = $document->created_at->format('Y/m/d');
                $now = Carbon::now();
                if ($now > Carbon::parse($date_of_due)) {
                    $delay_payment = $now->diffInDays(Carbon::parse($date_of_due));
                }
            } else {
                $web_platforms = new \Illuminate\Database\Eloquent\Collection();
            }
            $customer_internal_code = null;
            $customer_trade_name  = null;
            $customer_zone_name = null;
            $customer_telephone = null;
            $customer_address = null;
            $customer_email = null;
            $seller_name = null;
            $customer_ubigeo = null;
            if ($document) {
                $customer_internal_code = $document->customer->internal_code;
                $customer_number = $document->customer->number;
                $customer_trade_name  = $document->customer->trade_name;
                $customer_telephone = $document->customer->telephone;
                $customer_email = $document->customer->email;
                $customer_address = $document->customer->address;
                $customer_id = $document->customer_id;
                $customer_ubigeo = $document->customer->district_id;
                $zone = Person::find($customer_id)->getZone();
                $seller_name = $document->user->name;
                if ($zone) {
                    $customer_zone_name = $zone->name;
                }
            }
            $currency_type_id = $row->currency_type_id;
            $currency = CurrencyType::find($currency_type_id);
            $currency_symbol = ($currency) ? $currency->symbol : '';
            $customer_ubigeo_description = $customer_ubigeo ? func_get_location($customer_ubigeo) : null;
            if ($customer_ubigeo_description != '' && $customer_ubigeo_description != null) {
                $explode_ubigeo = explode(' - ', $customer_ubigeo_description);
                $customer_ubigeo_description = implode(' - ', array_reverse($explode_ubigeo));
            }
            return [
                'currency_symbol' => $currency_symbol,
                'line_credit' => $row->line_credit,
                'id' => $row->id,
                'seller_name' => $seller_name,
                'customer_number' => $customer_number,
                'date_of_issue' => $date_of_issue,
                'customer_name' => $row->customer_name,
                'customer_internal_code' => $customer_internal_code,
                'customer_telephone' => $customer_telephone,
                'customer_address' => $customer_address,
                'customer_email' => $customer_email,
                'customer_zone_name' => $customer_zone_name,
                'customer_trade_name' => $customer_trade_name,
                'customer_id' => $row->customer_id,
                'customer_ubigeo' => $customer_ubigeo,
                'customer_ubigeo_description' => $customer_ubigeo_description,
                'number_full' => $number_full,
                'total' => number_format((float) $row->total, 2, ".", ""),
                'total_to_pay' => number_format($total_to_pay, 2, ".", ""),
                'type' => $row->type,
                'guides' => $guides,
                'payments' => $payments,
                'date_payment_last' => ($date_payment_last) ? $date_payment_last->date_of_payment->format('Y-m-d') : null,
                'delay_payment' => $delay_payment,
                'date_of_due' =>  $date_of_due,
                'currency_type_id' => $row->currency_type_id,
                'exchange_rate_sale' => (float)$row->exchange_rate_sale,
                "user_id" => $row->user_id,
                "username" => $row->username,
                "total_subtraction" => $row->total_subtraction,
                "total_payment" => $row->total_payment,
                "purchase_order" => $purchase_order,
                "web_platforms" => $web_platforms,
                "total_credit_notes" => $this->getTotalCreditNote($row),
            ];
        });
    }
    public function transformRecordsByCustomer($records, $detail = null)
    {

        return $records->transform(function ($row, $key)  use ($detail) {
            $total_to_pay = $this->getTotalToPay($row);
            $date_of_issue = Carbon::parse($row->date_of_issue)->format('d/m/Y');
            $delay_payment = null;
            $date_of_due = null;

            if ($total_to_pay > 0) {
                if ($row->document_type_id) {

                    $invoice = Invoice::where('document_id', $row->id)->first();
                    if ($invoice) {
                        $due =   Carbon::parse($invoice->date_of_due); // $invoice->date_of_due;
                        $date_of_due = $invoice->date_of_due->format('d/m/Y');
                        $now = Carbon::now();

                        if ($now > $due) {

                            $delay_payment = $now->diffInDays($due);
                        }
                    }
                }
            }

            $date_payment_last = '';
            $payments = [];
            $doc_related = null;
            $code = null;
            // $number_full = $row->number_full;
            $number_full = null;
            if ($detail) {
                $payments = $this->getPayments($row);
            }
            if ($row->document_type_id) {


                $date_payment_last = DocumentPayment::where('document_id', $row->id)->orderBy('date_of_payment', 'desc')->first();
            } else {
                $date_payment_last = SaleNotePayment::where('sale_note_id', $row->id)->orderBy('date_of_payment', 'desc')->first();
            }
            if ($row->type == 'document') {
                $document = Document::find($row->id);
                $doc_related = $document->series . '-' . $document->number;
            } elseif ($row->type == 'sale_note') {
                $document = SaleNote::find($row->id);
                $now = Carbon::createFromFormat('d/m/Y', Carbon::now()->format('d/m/Y'));
                $doc_related = $document->series . '-' . $document->number;
                if ($document->due_date) {
                    $date_of_due = $document->due_date->format('d/m/Y');
                } else {
                    $date_of_due =  $document->date_of_issue->format('d/m/Y');
                }
                $parse_date_of_due = Carbon::createFromFormat('d/m/Y', $date_of_due);

                if ($now > $parse_date_of_due) {
                    $delay_payment = $now->diffInDays($parse_date_of_due);
                }
            } elseif ($row->type == 'bill_of_exchange') {
                $document = BillOfExchange::find($row->id);
                $code = $document->code;
                $number_full = $document->series . '-' . $document->number;
                $doc_related = $document->items->first()->document->number_full;
                $date_of_due = is_null($document->date_of_due) ? null : $document->date_of_due->format('d/m/Y');
                $date_of_issue = $document->created_at->format('d/m/Y');
                $delay_payment = 0;
                $parse_date_of_due = Carbon::createFromFormat('d/m/Y', $date_of_due);
                $now = Carbon::now();
                if ($now > $parse_date_of_due) {
                    $delay_payment = $now->diffInDays($parse_date_of_due);
                }
            } elseif ($row->type == 'document_fee') {
                $delay_payment = 0;
                $now = Carbon::now();
                $document = Document::select('documents.id', 'documents.date_of_issue', 'documents.series', 'documents.number')->find($row->id);
                $doc_related = $document->series . '-' . $document->number;
                $date_of_due = $row->date_of_issue;
                $date_of_issue = $document->date_of_issue->format('d/m/Y');
                $parse_date_of_due = Carbon::createFromFormat('Y/m/d', $date_of_due);
                if ($now > $parse_date_of_due) {
                    $delay_payment = $now->diffInDays($parse_date_of_due);
                }
            }

            // Verificar formato de date_of_due cuando es string
            if (is_string($date_of_due)) {
                $parts = explode('/', $date_of_due);

                // Buscar qué parte tiene 4 caracteres (año)
                foreach ($parts as $index => $part) {
                    if (strlen($part) == 4) {
                        // Si el año está primero, el formato es Y/m/d
                        if ($index == 0) {
                            $date_of_due = Carbon::createFromFormat('Y/m/d', $date_of_due)
                                ->format('d/m/Y');
                        }
                        break;
                    }
                }
            }

            return [
                'doc_related' => $doc_related,
                'number_full' => $number_full,
                'code' => $code,
                'date_of_issue' => $date_of_issue,
                'date_of_due' =>  $date_of_due,
                'currency_type_id' => $row->currency_type_id,
                'total_to_pay' => number_format($total_to_pay, 2, ".", ""),
                "total_payment" => $row->total_payment,
                'delay_payment' => $delay_payment,
                'customer_id' => $row->customer_id,
                'total' => number_format((float) $row->total, 2, ".", ""),
                'type' => $row->type,
                'payments' => $payments,
                'date_payment_last' => ($date_payment_last) ? $date_payment_last->date_of_payment->format('Y-m-d') : null,
                'currency_type_id' => $row->currency_type_id,
                'exchange_rate_sale' => (float)$row->exchange_rate_sale,
                "user_id" => $row->user_id,
                "username" => $row->username,
                "payed" => $row->total_subtraction,
                'total_subtraction' => $row->total_subtraction,
                'state' => $delay_payment > 0 ? "Vencido" : "Pendiente",
            ];
        });
    }
    function getPayments($row)
    {
        $id = $row->id;
        $type = $row->type;
        if ($type == 'document') {
            $payments = DocumentPayment::where('document_id', $id);
        } else if ($type == 'sale_note') {
            $payments = SaleNotePayment::where('sale_note_id', $id);
        } else if ($type == 'bill_of_exchange') {
            $payments = BillOfExchangePayment::where('bill_of_exchange_id', $id);
        } else {
            return [];
        }
        
        $payments = $payments->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'date_of_payment' => $row->date_of_payment->format('Y-m-d'),
                'payment' => $row->payment,
                'payment_method_type_description' => $row->payment_method_type->description,
            ];
        });
        if($type == 'document'){
                $has_note = Note::where('affected_document_id', $id)->first();            
                if($has_note){
                    $payments->push([
                        'id' => $has_note->id,
                        'date_of_payment' => $has_note->document->format('Y-m-d'),
                        'payment' => $has_note->document->total,
                        'payment_method_type_description' => 'Nota de crédito',
                    ]);
                }
        }


        return $payments;
    }
    /**
     * Obtener total por cobrar
     *
     * @param  object $row
     * @return float
     */
    public function getTotalToPay($row)
    {
        return (float)$row->total - (float)$row->total_payment - (float) $this->getTotalCreditNote($row);
    }


    /**
     * Validar y obtener total nota credito
     *
     * @param  object $row
     * @return float
     */
    public function getTotalCreditNote($row)
    {
        return ($row->total_credit_notes ?? 0);
    }

    /**
     * Versión optimizada de transformRecords3 para mejor rendimiento
     * 
     * @param \Illuminate\Support\Collection $records
     * @param bool|null $detail
     * @return \Illuminate\Support\Collection
     */
    public function transformRecords3Optimized($records, $detail = null)
    {
        // Extraer IDs para consultas masivas

        $documentIds = $records->where('document_type_id', '!=', null)->pluck('id')->toArray();
        $saleNoteIds = $records->where('type', 'sale_note')->pluck('id')->toArray();
        $billOfExchangeIds = $records->where('type', 'bill_of_exchange')->pluck('id')->toArray();
        $documentFeeIds = $records->where('type', 'document_fee')->pluck('id')->toArray();
        $customerIds = $records->pluck('customer_id')->unique()->filter()->toArray();

        // Pre-cargar datos en consultas masivas
        $invoices = collect();
        if (!empty($documentIds)) {
            $invoices = Invoice::whereIn('document_id', $documentIds)->get()->keyBy('document_id');
        }

        // Pre-cargar últimos pagos para documentos
        $lastDocumentPayments = collect();
        if (!empty($documentIds)) {
            $lastDocumentPayments = DocumentPayment::whereIn('document_id', $documentIds)
                ->select('document_id', 'date_of_payment')
                ->orderBy('date_of_payment', 'desc')
                ->get()
                ->groupBy('document_id')
                ->map(function ($payments) {
                    return $payments->first();
                });
        }

        // Pre-cargar últimos pagos para sale notes
        $lastSaleNotePayments = collect();
        if (!empty($saleNoteIds)) {
            $lastSaleNotePayments = SaleNotePayment::whereIn('sale_note_id', $saleNoteIds)
                ->select('sale_note_id', 'date_of_payment')
                ->orderBy('date_of_payment', 'desc')
                ->get()
                ->groupBy('sale_note_id')
                ->map(function ($payments) {
                    return $payments->first();
                });
        }

        // Pre-cargar documentos relacionados
        $documents = collect();
        if (!empty($documentIds)) {
            $documents = Document::whereIn('id', $documentIds)
                ->with([ 'user'])
                ->get()
                ->keyBy('id');
        }

        // Pre-cargar sale notes relacionadas
        $saleNotes = collect();
        if (!empty($saleNoteIds)) {
            $saleNotes = SaleNote::whereIn('id', $saleNoteIds)
                ->with(['user'])
                ->get()
                ->keyBy('id');
        }

        // Pre-cargar bill of exchanges
        $billOfExchanges = collect();
        if (!empty($billOfExchangeIds)) {
            $billOfExchanges = BillOfExchange::whereIn('id', $billOfExchangeIds)->get()->keyBy('id');
        }

        // Pre-cargar document fees
        $documentFees = collect();
        if (!empty($documentFeeIds)) {
            $documentFees = DocumentFee::whereIn('id', $documentFeeIds)
                ->get()
                ->keyBy('id');
        }

        // Pre-cargar zonas de clientes
        $customerZones = collect();
        if (!empty($customerIds)) {
            $customerZones = Person::whereIn('id', $customerIds)
                ->get()
                ->keyBy('id')
                ->map(function ($person) {
                    return $person->getZone();
                });
        }

        // Pre-cargar tipos de moneda
        $currencyTypeIds = $records->pluck('currency_type_id')->unique()->filter()->toArray();
        $currencies = collect();
        if (!empty($currencyTypeIds)) {
            $currencies = CurrencyType::whereIn('id', $currencyTypeIds)->get()->keyBy('id');
        }

        $now = Carbon::now();

        return $records->transform(function ($row, $key) use (
            $detail, 
            $invoices, 
            $lastDocumentPayments, 
            $lastSaleNotePayments,
            $documents,
            $saleNotes,
            $billOfExchanges,
            $documentFees,
            $customerZones,
            $currencies,
            $now
        ) {
            $description = null;
            $code = null;
            $total_to_pay = $this->getTotalToPay($row);
            $date_of_issue = $row->date_of_issue;
            $delay_payment = null;
            $date_of_due = null;

            // Verificar invoice para documentos
            if ($total_to_pay > 0 && $row->document_type_id) {
                $invoice = $invoices->get($row->id);
                if ($invoice) {
                    $due = Carbon::parse($invoice->date_of_due);
                    $date_of_due = $invoice->date_of_due->format('Y/m/d');
                    if ($now > $due) {
                        $delay_payment = $now->diffInDays($due);
                    }
                }
            }

            $date_payment_last = null;
            $payments = [];
            $number_full = $row->number_full;
            
            if ($detail) {
                $payments = $this->getPayments($row);
            }

            // Obtener último pago
            if ($row->document_type_id) {
                $date_payment_last = $lastDocumentPayments->get($row->id);
            } else {
                $date_payment_last = $lastSaleNotePayments->get($row->id);
            }

            $customer_trade_name = null;
            $customer_zone_name = null;
            $customer_address = null;
            $customer_email = null;
            $seller_name = null;
            $document_related = null;
            $document = null;

            // Procesar según el tipo
            if ($row->type == 'document') {
                
                $document = $documents->get($row->id);
                $date_of_due = isset($document->invoice->date_of_due) ? $document->invoice->date_of_due->format('Y/m/d') : null;
                $description = $document->number_full;
                $document_related = null;
            } elseif ($row->type == 'sale_note') {
                $delay_payment = null;
                $document = $saleNotes->get($row->id);
                if ($document) {
                    if ($document->due_date) {
                        $date_of_due = $document->due_date->format('Y/m/d');
                    } else {
                        $date_of_due = $document->date_of_issue->format('Y/m/d');
                    }
                    if ($now > Carbon::parse($date_of_due)) {
                        $delay_payment = $now->diffInDays(Carbon::parse($date_of_due));
                    }
                }
            } elseif ($row->type == 'bill_of_exchange') {
                $delay_payment = null;
                $billOfExchange = $billOfExchanges->get($row->id);
                if ($billOfExchange) {
                    $number_full = $billOfExchange->number_full;
                    $description = $billOfExchange->series . '-' . $billOfExchange->number;
                    $code = $billOfExchange->code;
                    $items = $billOfExchange->items;
                    if($items->count() > 0){
                        $document_r = "";
                        foreach($items as $item){
                            $document_r .= $item->document->number_full . ", ";
                        }
                        $document_related = $document_r;
                        $document_related = rtrim($document_r, ", ");
                    }
                    $date_of_due = is_null($billOfExchange->date_of_due) ? null : $billOfExchange->date_of_due->format('Y/m/d');
                    $date_of_issue = $billOfExchange->created_at->format('Y/m/d');
                    if($now > Carbon::parse($date_of_due)){
                        $delay_payment = $now->diffInDays($date_of_due);
                    }
                }
            } elseif ($row->type == 'document_fee') {
                $delay_payment = null;
                $documentFee = $documentFees->get($row->id);
                $description = "Cuota #".$row->id;
                if ($documentFee) {
                    $document = $documentFee->document;
                    $number_full = $document->number_full;
                    $date_of_due =  $documentFee->date->format('Y/m/d');
                    $date_of_issue = $document->created_at->format('Y/m/d');
                    $document_related = $document->series . '-' . $document->number;
                    if($documentFee->date < $now){
                        $delay_payment = $now->diffInDays($date_of_due);
                        
                    }
                }
            }

            // Procesar información del cliente y vendedor
            if ($document) {
                $customer_trade_name = $document->customer->trade_name ?? null;
                $customer_email = $document->customer->email ?? null;
                $customer_address = $document->customer->address ?? null;
                $customer_id = $document->customer_id;
                $seller_name = $document->user->name ?? null;
                
                $zone = $customerZones->get($customer_id);
                if ($zone) {
                    $customer_zone_name = $zone->name;
                }
                
            }

            $currency_symbol = $currencies->get($row->currency_type_id)->symbol ?? '';

            return [
                'document_related' => $document_related,
                'currency_symbol' => $currency_symbol,
                'id' => $row->id,
                'description' => $description,
                'code' => $code,
                'seller_name' => $seller_name,
                'date_of_issue' => $date_of_issue,
                'customer_name' => $row->customer_name,
                'customer_address' => $customer_address,
                'customer_email' => $customer_email,
                'customer_zone_name' => $customer_zone_name,
                'customer_trade_name' => $customer_trade_name,
                'customer_id' => $row->customer_id,
                'number_full' => $number_full,
                'total' => number_format((float) $row->total, 2, ".", ""),
                'total_to_pay' => number_format($total_to_pay, 2, ".", ""),
                'type' => $row->type,
                'payments' => $payments,
                'date_payment_last' => ($date_payment_last) ? $date_payment_last->date_of_payment->format('Y-m-d') : null,
                'delay_payment' => $delay_payment,
                'date_of_due' =>  $date_of_due,
                'currency_type_id' => $row->currency_type_id,
                'exchange_rate_sale' => (float)$row->exchange_rate_sale,
                "user_id" => $row->user_id,
                "username" => $row->username,
                "total_subtraction" => $row->total_subtraction,
                "total_payment" => $row->total_payment,
            ];
        });
    }

    /**
     * Versión ultra-optimizada para PDF - mínimo uso de memoria
     *
     * @param \Illuminate\Support\Collection $records
     * @return \Illuminate\Support\Collection
     */
    public function transformRecords3OptimizedForPdf($records)
    {
        $now = Carbon::now();
        $results = collect();

        // Procesar cada registro individualmente para usar menos memoria
        foreach ($records as $row) {
            $total_to_pay = $this->getTotalToPay($row);

            // Filtrar temprano - si no hay saldo, continuar
            if ($total_to_pay <= 0) {
                continue;
            }

            $description = null;
            $code = null;
            $date_of_issue = $row->date_of_issue;
            $delay_payment = 0;
            $date_of_due = null;
            $document_related = null;
            $customer_ruc = '-';
            $customer_name = '-';
            $customer_telephone = '-';
            $customer_zone = '-';
            $seller_name = '-';
            $line_credit = '-';

            // Procesar según el tipo de documento con consultas individuales minimalistas
            if ($row->type == 'document' && $row->document_type_id) {
                // Cargar solo datos esenciales del documento
                $document = Document::select('id', 'series', 'number', 'customer_id', 'user_id')
                    ->with([
                        'person:id,number,name,trade_name,telephone,address,zone_id,line_credit',
                        'person.zoneRelation:id,name',
                        'user:id,name'
                    ])
                    ->find($row->id);

                if ($document) {
                    $description = $document->series . '-' . $document->number;
                    $document_related = $description; // Usar el mismo valor

                    if ($document->person) {
                        $customer_ruc = $document->person->number ?? '-';
                        $customer_name = $document->person->name ?? '-';
                        $customer_telephone = $document->person->telephone ?? '-';
                        $customer_zone = $document->person->zoneRelation ? $document->person->zoneRelation->name : '-';
                        $line_credit = $document->person->line_credit ?? '-';
                    }

                    if ($document->user) {
                        $seller_name = $document->user->name ?? '-';
                    }

                    // Cargar invoice solo si es necesario para calcular días de atraso
                    $invoice = Invoice::select('date_of_due')->where('document_id', $row->id)->first();
                    if ($invoice && $invoice->date_of_due) {
                        $due = Carbon::parse($invoice->date_of_due);
                        $date_of_due = $invoice->date_of_due->format('Y/m/d');
                        if ($now > $due) {
                            $delay_payment = $now->diffInDays($due);
                        } else {
                            $delay_payment = 0; // No vencido
                        }
                    }
                }

            } elseif ($row->type == 'sale_note') {
                $document = SaleNote::select('id', 'due_date', 'date_of_issue', 'series', 'number', 'customer_id', 'user_id')
                    ->with([
                        'person:id,number,name,trade_name,telephone,address,zone_id,line_credit',
                        'person.zoneRelation:id,name',
                        'user:id,name'
                    ])
                    ->find($row->id);

                if ($document) {
                    $description = $document->series . '-' . $document->number;
                    $document_related = $description; // Usar el mismo valor

                    $date_of_due = $document->due_date ?
                        $document->due_date->format('Y/m/d') :
                        $document->date_of_issue->format('Y/m/d');

                    if ($now > Carbon::parse($date_of_due)) {
                        $delay_payment = $now->diffInDays(Carbon::parse($date_of_due));
                    } else {
                        $delay_payment = 0; // No vencido
                    }

                    if ($document->person) {
                        $customer_ruc = $document->person->number ?? '-';
                        $customer_name = $document->person->name ?? '-';
                        $customer_telephone = $document->person->telephone ?? '-';
                        $customer_zone = $document->person->zoneRelation ? $document->person->zoneRelation->name : '-';
                        $line_credit = $document->person->line_credit ?? '-';
                    }

                    if ($document->user) {
                        $seller_name = $document->user->name ?? '-';
                    }
                }

            } elseif ($row->type == 'bill_of_exchange') {
                // Procesar bill of exchange
                $billOfExchange = BillOfExchange::select('id', 'series', 'number', 'code', 'date_of_due', 'created_at', 'customer_id', 'user_id')
                    ->with([
                        'person:id,number,name,trade_name,telephone,address,zone_id,line_credit',
                        'person.zoneRelation:id,name',
                        'user:id,name',
                        'items:id,bill_of_exchange_id,document_id',
                        'items.document:id,series,number'
                    ])
                    ->find($row->id);

                if ($billOfExchange) {
                    $description = $billOfExchange->series . '-' . $billOfExchange->number;
                    $code = $billOfExchange->code;
                    $date_of_issue = $billOfExchange->created_at->format('Y/m/d');

                    // Obtener documentos relacionados
                    $document_related = $billOfExchange->items->map(function($item) {
                        return $item->document->series . '-' . $item->document->number;
                    })->implode(', ');

                    $date_of_due = $billOfExchange->date_of_due ?
                        $billOfExchange->date_of_due->format('Y/m/d') : null;

                    if ($date_of_due && $now > Carbon::parse($date_of_due)) {
                        $delay_payment = $now->diffInDays(Carbon::parse($date_of_due));
                    } else {
                        $delay_payment = 0;
                    }

                    if ($billOfExchange->person) {
                        $customer_ruc = $billOfExchange->person->number ?? '-';
                        $customer_name = $billOfExchange->person->name ?? '-';
                        $customer_telephone = $billOfExchange->person->telephone ?? '-';
                        $customer_zone = $billOfExchange->person->zoneRelation ? $billOfExchange->person->zoneRelation->name : '-';
                        $line_credit = $billOfExchange->person->line_credit ?? '-';  
                    }

                    if ($billOfExchange->user) {
                        $seller_name = $billOfExchange->user->name ?? '-';
                    }
                }

            } elseif ($row->type == 'document_fee') {
                // Procesar document fee (cuotas)
                $documentFee = DocumentFee::select('id', 'document_id', 'date')
                    ->with([
                        'document:id,series,number,created_at,customer_id,user_id',
                        'document.person:id,number,name,trade_name,telephone,address,zone_id,line_credit',
                        'document.person.zoneRelation:id,name',
                        'document.user:id,name'
                    ])
                    ->find($row->id);

                if ($documentFee) {
                    $description = "";
                    $document_related = $documentFee->document->series . '-' . $documentFee->document->number;
                    $date_of_due = $documentFee->date->format('Y/m/d');
                    $date_of_issue = $documentFee->document->created_at->format('Y/m/d');

                    if ($documentFee->date < $now) {
                        $delay_payment = $now->diffInDays($documentFee->date);
                    } else {
                        $delay_payment = 0;
                    }

                    if ($documentFee->document && $documentFee->document->person) {
                        $customer_ruc = $documentFee->document->person->number ?? '-';
                        $customer_name = $documentFee->document->person->name ?? '-';
                        $customer_telephone = $documentFee->document->person->telephone ?? '-';
                        $customer_zone = $documentFee->document->person->zoneRelation ? $documentFee->document->person->zoneRelation->name : '-';
                        $line_credit = $documentFee->document->person->line_credit ?? '-';
                    }

                    if ($documentFee->document && $documentFee->document->user) {
                        $seller_name = $documentFee->document->user->name ?? '-';
                    }
                }

            } else {
                // Para otros tipos, usar datos básicos del row
                $description = $row->number_full ?? '-';
                $code = $row->code ?? null;
                $delay_payment = 0; // Sin atraso para tipos no identificados
            }

            $results->push([
                'document_related' => $document_related ?? '-',
                'description' => $description ?? '-',
                'code' => $code ?? '-',
                'date_of_issue' => $date_of_issue ?? '-',
                'date_of_due' => $date_of_due ?? '-',
                'currency_type_id' => $row->currency_type_id ?? 'PEN',
                'total' => number_format((float) $row->total, 2, ".", ""),
                'total_payment' => number_format((float) $row->total_payment, 2, ".", ""),
                'total_subtraction' => number_format((float) $row->total_subtraction, 2, ".", ""),
                'total_to_pay' => number_format($total_to_pay, 2, ".", ""),
                'delay_payment' => $delay_payment, // Este es el campo importante que faltaba
                'customer_ruc' => $customer_ruc,
                'customer_name' => $customer_name,
                'customer_telephone' => $customer_telephone,
                'customer_zone' => $customer_zone,
                'seller_name' => $seller_name,
                'line_credit' => $line_credit,
            ]);

            // Liberar memoria después de cada registro
            unset($document, $invoice, $billOfExchange, $documentFee);
        }

        return $results;
    }
}

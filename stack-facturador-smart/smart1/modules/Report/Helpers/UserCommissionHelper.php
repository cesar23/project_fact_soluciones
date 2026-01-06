<?php

namespace Modules\Report\Helpers;

use App\Models\Tenant\Document;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\Person;
use App\Models\Tenant\DocumentItem;
use App\Models\Tenant\PurchaseItem;
use App\Models\Tenant\SaleNoteItem;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\Item;
use Carbon\Carbon;
use App\CoreFacturalo\Helpers\Functions\FunctionsHelper;


class UserCommissionHelper
{

    public static function getCommission($user, $utilities)
    {

        $type = $user->user_commission->type;
        $amount = $user->user_commission->amount;

        $commission = ($type == 'amount') ? $utilities['total_utility'] * $amount : ($utilities['total_utility'] * ($amount / 100));

        return number_format($commission, 2, ".", "");
    }


    public static function getUtilities($sale_notes, $documents)
    {
        $sale_notes_utility = self::getUtilityRecords($sale_notes);
        $documents_utility = self::getUtilityRecords($documents);

        return [
            'sale_notes_utility' => number_format($sale_notes_utility, 2, ".", ""),
            'documents_utility' => number_format($documents_utility, 2, ".", ""),
            'total_utility' => number_format($documents_utility + $sale_notes_utility, 2, ".", ""),
        ];
    }


    public static function getUtilityRecords($records)
    {

        return $records->sum(function ($record) {

            return $record->items->sum(function ($item) use ($record) {

                $total_item_purchase = self::getPurchaseUnitPrice($item) * $item->quantity;
                $total_item_sale = self::calculateTotalCurrencyType($record, $item->total);
                $total_item = $total_item_sale - $total_item_purchase;

                return ($record->document_type_id === '07') ? $total_item * -1 : $total_item;
            });
        });
    }


    public static function getPurchaseUnitPrice($record)
    {

        $purchase_unit_price = 0;

        if ($record->item->unit_type_id != 'ZZ') {

            if ($record->relation_item->purchase_unit_price > 0) {

                $purchase_unit_price = $record->relation_item->purchase_unit_price;
            } else {

                $purchase_item = PurchaseItem::select('unit_price')->where('item_id', $record->item_id)->latest('id')->first();
                $purchase_unit_price = ($purchase_item) ? $purchase_item->unit_price : $record->unit_price;
            }
        }

        return $purchase_unit_price;
    }

    public static function calculateTotalCurrencyType($record, $total)
    {
        return ($record->currency_type_id == 'USD') ? $total * $record->exchange_rate_sale : $total;
    }


    /**
     * 
     * Obtener totales para reporte de comisiones
     * Usado en:
     * Modules\Report\Http\Resources\ReportCommissionCollection
     * Formato excel y pdf (blade) de reportes comisiones
     *
     * @param $user
     * @return array
     */
    public static function getDataForReportCommission($user, $request)
    {
        $requestInner = $request->all();
        $date_start = $requestInner['date_start'];
        $date_end = $requestInner['date_end'];
        $establishment_id = $requestInner['establishment_id'];
        $user_type = $requestInner['user_type'];
        $user_seller_id = $requestInner['user_seller_id'];
        $item_id = $requestInner['item_id'];
        $unit_type_id = $requestInner['unit_type_id'];
        $row_user_id = $user->id;


        FunctionsHelper::setDateInPeriod($requestInner, $date_start, $date_end);

        //si user_seller_id es null, en la consulta se usara el id del usuario de la fila

        $documents = Document::whereFilterCommission($date_start, $date_end, $establishment_id, $user_type, $user_seller_id, $row_user_id, $item_id, $unit_type_id)->get();

        $sale_notes = SaleNote::whereFilterCommission($date_start, $date_end, $establishment_id, $user_type, $user_seller_id, $row_user_id, $item_id, $unit_type_id)->get();

        $total_commision = 0;

    

        $total_transactions_document = $documents->count();
        $total_transactions_sale_note = $sale_notes->count();

        $acum_sales_document = $documents->sum(function ($document) {
            $total = $document->getTotalByDocumentType();
            if($document->currency_type_id == 'USD'){
                $total = $total * $document->exchange_rate_sale;
            }
            return $total;
        });
        $acum_sales_sale_note = $sale_notes->sum(function ($sale_note) {
            $total = $sale_note->total;
            if($sale_note->currency_type_id == 'USD'){
                $total = $total * $sale_note->exchange_rate_sale;
            }
            return $total;
        });
        $total_commision_document = self::getTotalCommision($documents, $item_id, $unit_type_id);
        $total_commision_sale_note = self::getTotalCommision($sale_notes, $item_id, $unit_type_id);

        return [
            'id' => $user->id,
            'user_name' => $user->name,

            'acum_sales' => number_format($acum_sales_document + $acum_sales_sale_note, 2),
            'acum_sales_document' => $acum_sales_document,
            'acum_sales_sale_note' => $acum_sales_sale_note,

            'total_commision' => number_format($total_commision_document + $total_commision_sale_note, 2),
            'total_commision_sale_note' => $total_commision_sale_note,
            'total_commision_document' => $total_commision_document,

            'total_transactions' => $total_transactions_document + $total_transactions_sale_note,
            'total_transactions_document' => $total_transactions_document,
            'total_transactions_sale_note' => $total_transactions_sale_note,
        ];
    }
    public static function getDataForReportCommissionDetailedV2($user, $request)
    {
        $requestInner = $request->all();
        $date_start = $requestInner['date_start'];
        $date_end = $requestInner['date_end'];
        $establishment_id = $requestInner['establishment_id'];
        $user_type = $requestInner['user_type'];
        $user_seller_id = $requestInner['user_seller_id'];
        $item_id = $requestInner['item_id'];
        $unit_type_id = $requestInner['unit_type_id'];
        $row_user_id = $user->id;

        FunctionsHelper::setDateInPeriod($requestInner, $date_start, $date_end);

        $documents = Document::whereFilterCommissionV2($date_start, $date_end, $establishment_id, $user_type, $user_seller_id, $row_user_id, $item_id, $unit_type_id)->get();
        $sale_notes = SaleNote::whereFilterCommissionV2($date_start, $date_end, $establishment_id, $user_type, $user_seller_id, $row_user_id, $item_id, $unit_type_id)->get();

        // Obtener y agrupar items
        $all_documents = collect();

        foreach ($documents as $document) {
            $seller_name = $document->seller ? $document->seller->name : $document->user->name;
            $total_document = 0;
            $total_commision = 0;
            $commision = 0;
            foreach ($document->items as $item) {
                if ($item->affectation_igv_type_id == '15') continue;
                $total_item = ($document->document_type_id === '07') ? $item->total * -1 : $item->total;
                if($document->currency_type_id == 'USD'){
                    $total_item = $total_item * $document->exchange_rate_sale;
                }
                $total_document += $total_item;
                if ($item->relation_item->commission_amount) {
                    if (!$item->relation_item->commission_type || $item->relation_item->commission_type == 'amount') {
                        $commision += $item->quantity * $item->relation_item->commission_amount;
                    } else {
                        $commision += $item->quantity * $item->unit_price * ($item->relation_item->commission_amount / 100);
                    }
                }
            }
            $total_commision = ($document->document_type_id === '07') ? $commision * -1 : $commision;
            $all_documents->push((object)[
                'date_of_issue' => $document->date_of_issue->format('Y-m-d'),
                'customer_name' => $document->customer->name,
                'customer_number' => $document->customer->number,
                'number_full' => $document->number_full,
                'quantity' => 1,
                'document_id' => $document->id,
                'document_type' => $document->document_type_id,
                'total' => $total_document,
                'total_commision' => $total_commision,
                'seller_name' => $seller_name,
            ]);
        }
        foreach ($sale_notes as $sale_note) {
            $seller_name = $sale_note->seller ? $sale_note->seller->name : $sale_note->user->name;
            $total_sale_note = 0;
            $total_commision = 0;
            $commision = 0;
            foreach ($sale_note->items as $item) {
                if ($item->affectation_igv_type_id == '15') continue;
                $total = $item->total;
                if($sale_note->currency_type_id == 'USD'){
                    $total = $total * $sale_note->exchange_rate_sale;   
                }
                $total_sale_note += $total;
                if ($item->relation_item->commission_amount) {
                    if (!$item->relation_item->commission_type || $item->relation_item->commission_type == 'amount') {
                        $commision += $item->quantity * $item->relation_item->commission_amount;
                    } else {
                        $commision += $item->quantity * $item->unit_price * ($item->relation_item->commission_amount / 100);
                    }
                }
            }
            $total_commision =  $commision;
            $all_documents->push((object)[
                'date_of_issue' => $sale_note->date_of_issue->format('Y-m-d'),
                'customer_name' => $sale_note->customer->name,
                'customer_number' => $sale_note->customer->number,
                'number_full' => $sale_note->number_full,
                'quantity' => 1,
                'document_id' => $sale_note->id,
                'document_type' => $sale_note->document_type_id,
                'total' => $total_sale_note,
                'total_commision' => $total_commision,
                'seller_name' => $seller_name,
            ]);
        }

    
        return [
            'id' => $user->id,
            'user_name' => $user->name,
            'items' => $all_documents,
        
        ];
    }
    public static function getDataForReportCommissionDetailed($user, $request)
    {
        $requestInner = $request->all();
        $date_start = $requestInner['date_start'];
        $date_end = $requestInner['date_end'];
        $establishment_id = $requestInner['establishment_id'];
        $user_type = $requestInner['user_type'];
        $user_seller_id = $requestInner['user_seller_id'];
        $item_id = $requestInner['item_id'];
        $unit_type_id = $requestInner['unit_type_id'];
        $row_user_id = $user->id;

        FunctionsHelper::setDateInPeriod($requestInner, $date_start, $date_end);

        $documents = Document::whereFilterCommission($date_start, $date_end, $establishment_id, $user_type, $user_seller_id, $row_user_id, $item_id, $unit_type_id)->get();
        $sale_notes = SaleNote::whereFilterCommission($date_start, $date_end, $establishment_id, $user_type, $user_seller_id, $row_user_id, $item_id, $unit_type_id)->get();

        // Obtener y agrupar items
        $items = collect();

        foreach ($documents as $document) {
            foreach ($document->items as $item) {
                $quantity = ($document->document_type_id === '07') ? $item->quantity * -1 : $item->quantity;
                $total = $item->total;
                if($document->currency_type_id == 'USD'){
                    $total = $total * $document->exchange_rate_sale;
                }
                $existingItem = $items->where('item_id', $item->item_id)->first();
                if ($existingItem) {
                    $existingItem->quantity += $quantity;
                    $existingItem->total += $total;
                } else {
                    $items->push((object)[
                        'item_id' => $item->item_id,
                        'item_name' => $item->item->description,
                        'quantity' => $quantity,
                        'total' => $total,
                        'unit_type_id' => $item->item->unit_type_id,
                    ]);
                }
            }
        }
        foreach ($sale_notes as $sale_note) {
            foreach ($sale_note->items as $item) {
                $total = $item->total;
                if($sale_note->currency_type_id == 'USD'){
                    $total = $total * $sale_note->exchange_rate_sale;
                }
                $existingItem = $items->where('item_id', $item->item_id)->first();
                if ($existingItem) {
                    $existingItem->quantity += $item->quantity;
                    $existingItem->total += $total;
                } else {
                    $items->push((object)[
                        'item_id' => $item->item_id,
                        'item_name' => $item->item->description,
                        'quantity' => $item->quantity,
                        'total' => $total,
                        'unit_type_id' => $item->item->unit_type_id,
                    ]);
                }
            }
        }

        $total_transactions_document = $documents->count();
        $total_transactions_sale_note = $sale_notes->count();

        $acum_sales_document = $documents->sum(function ($document) {
            $total = $document->getTotalByDocumentType();
            if($document->currency_type_id == 'USD'){
                $total = $total * $document->exchange_rate_sale;
            }
            return $total;
        });
        $acum_sales_sale_note = $sale_notes->sum(function ($sale_note) {
            $total = $sale_note->total;
            if($sale_note->currency_type_id == 'USD'){
                $total = $total * $sale_note->exchange_rate_sale;
            }
            return $total;
        });
        $total_commision_document = self::getTotalCommision($documents, $item_id, $unit_type_id);
        $total_commision_sale_note = self::getTotalCommision($sale_notes, $item_id, $unit_type_id);

        return [
            'id' => $user->id,
            'user_name' => $user->name,
            'items' => $items,
            'acum_sales' => number_format($acum_sales_document + $acum_sales_sale_note, 2),
            'total_commision' => number_format($total_commision_document + $total_commision_sale_note, 2),
            'total_transactions' => $total_transactions_document + $total_transactions_sale_note,
        ];
    }

    /**
     * Obtener total de comisiones
     * 
     * Usado para:
     * Documents
     * SaleNotes
     *
     * @param  mixed $records
     * @return float
     */
    public static function getTotalCommision($records, $item_id, $unit_type_id)
    {
        //dd($records);
        return $records->sum(function ($record) use ($item_id, $unit_type_id) {
            //dd($record->items,$item_id,$unit_type_id);

            return $record->items->sum(function ($item) use ($record) {
                $total_commision = 0;
                if ($item->relation_item->commission_amount) {
                    if (!$item->relation_item->commission_type || $item->relation_item->commission_type == 'amount') {
                        $total_commision = $item->quantity * $item->relation_item->commission_amount;
                    } else {
                        $total_commision = $item->quantity * $item->unit_price * ($item->relation_item->commission_amount / 100);
                    }
                }
                $total = ($record->document_type_id === '07') ? $total_commision * -1 : $total_commision;
                if($record->currency_type_id == 'USD'){
                    $total = $total * $record->exchange_rate_sale;
                }
                return $total;
            });
        });
    }
}



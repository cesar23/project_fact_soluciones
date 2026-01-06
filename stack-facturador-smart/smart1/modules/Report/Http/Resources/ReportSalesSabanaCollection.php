<?php

namespace Modules\Report\Http\Resources;

use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\Catalogs\IdentityDocumentType;
use App\Models\Tenant\Item;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\Person;
use App\Models\Tenant\PurchaseItem;
use App\Models\Tenant\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Hotel\Models\HotelRent;
use Modules\Order\Models\OrderNote;

class ReportSalesSabanaCollection extends ResourceCollection
{


    public function toArray($request)
    {


        return $this->collection->transform(function ($row, $key) {
            $item_id = $row->item_id;
            $seller_name = null;
            $document_type = null;
            $document_number = null;
            $supplier_name = null;
            $date_of_issue = null;
            $item_internal_id = null;
            $item_description = null;
            $unit_type_id = null;
            $customer_internal_code = null;
            $customer_name = null;
            $payment_condition = null;
            $unit_value = null;
            $unit_price = null;
            $total = null;
            $quantity = null;
            $seller_name = $row->seller_id ? User::find($row->seller_id)->name : null;
            $document_type = DocumentType::find($row->document_type_id)->description;
            $document_number = $row->number_full;
            $last_purchase_item = PurchaseItem::where('item_id', $item_id)
                ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
                ->orderBy('purchases.date_of_issue', 'desc')
                ->select('purchase_items.*')
                ->first();
            $last_purchase_document = $last_purchase_item ? $last_purchase_item->purchase : null;
            $supplier_name = $last_purchase_document ? $last_purchase_document->supplier->name : null;
            $date_of_issue = $row->date_of_issue;
            $item = json_decode($row->item);
            $item_internal_id = isset($item->internal_id) ? $item->internal_id : null;
            if ($item_internal_id == null) {
                $item_db = Item::find($item_id);
                if ($item_db) {
                    $item_internal_id = $item_db->internal_id;
                }
            }
            $item_description = $item->description;
            $unit_type_id = $item->unit_type_id;
            $customer = Person::find($row->customer_id);
            $customer_internal_code = $customer->internal_code;
            $customer_name = $customer->name;
            $payment_condition = $row->payment_condition_id == '01' ? 'Contado' : 'Crédito';
            if ($row->payment_condition_id == '02' && $row->order_note_id) {
                $order_note = OrderNote::find($row->order_note_id);
                $payment_method = PaymentMethodType::find($order_note->payment_method_type_id);
                if ($payment_method->is_credit){
                    $payment_condition = $payment_method->description;
                } 
            }
            $unit_value = $row->unit_value;
            $unit_price = $row->unit_price;
            $total = $row->unit_price * $row->quantity;
            $quantity = $row->quantity;

            return  [
                "seller_name" => $seller_name,
                "document_type" => $document_type,
                "document_number" => $document_number,
                "supplier_name" => $supplier_name,
                "date_of_issue" => $date_of_issue,
                "item_internal_id" => $item_internal_id,
                "item_description" => $item_description,
                "unit_type_id" => $unit_type_id,
                "customer_internal_code" => $customer_internal_code,
                "customer_name" => $customer_name,
                "payment_condition" => $payment_condition,
                "unit_value" => number_format($unit_value, 2),
                "unit_price" => number_format($unit_price, 2),
                "total" => $total,
                "quantity" => $quantity,
            ];
        });
    }
}

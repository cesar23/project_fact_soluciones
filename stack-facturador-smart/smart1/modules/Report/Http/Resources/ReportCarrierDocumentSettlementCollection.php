<?php

namespace Modules\Report\Http\Resources;

use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\Catalogs\IdentityDocumentType;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\Person;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Hotel\Models\HotelRent;

class ReportCarrierDocumentSettlementCollection extends ResourceCollection
{


    public function toArray($request)
    {


        return $this->collection->transform(function ($row, $key) {
            $payment_method_type_id = $row->payment_method_type_id;
            $customer_id = $row->customer_id;
            $total_due  = Person::getAccumulatedDue($customer_id);
            $payment_method_explode = explode(",", $payment_method_type_id);
            $payment_method_description = "";
            foreach ($payment_method_explode as $item) {
                $payment_method = PaymentMethodType::find($item);
                $payment_method_description .= ($payment_method) ? $payment_method->description . ', ' : '';
            }
            $document_type_id = $row->document_type_id;
            $document_number = $row->number_full;
            $customer_internal_id = $row->customer_internal_id;
            $customer_name = $row->customer_name;
            $sale_price = $row->total;
            $payment_method = null;
            $document_type = DocumentType::find($document_type_id);
            $document_type_description = ($document_type) ? $document_type->description : '';
            if(strlen($payment_method_description) > 0){
                $payment_method_description = substr($payment_method_description, 0, -2);
            }
            $seller_code = null;
            if($row->seller_number){
                $seller_code = $row->seller_number;
            }else{
                $seller_code = $row->seller_name;
            }
            return  [
                'payment_condition' => $row->payment_condition_id == '01' ? 'Contado' : 'Crédito',
                'seller_code' => $seller_code, // 'Código de vendedor
                'payment_method_description' => $payment_method_description, // 'Método de pago
                'document_type_description' => $document_type_description, // 'Tipo de documento
                "document_type_id" => $document_type_id,
                "document_number" => $document_number,
                "customer_internal_id" => $customer_internal_id,
                "customer_name" => $customer_name,
                "sale_price" => $sale_price,
                "payment_method" => $payment_method,
                "due" => $total_due,
            ];
        });
    }
}

<?php

namespace Modules\Report\Http\Resources;

use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\Catalogs\IdentityDocumentType;
use App\Models\Tenant\Item;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\Person;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Hotel\Models\HotelRent;

class ReportCarrierDocumentCollection extends ResourceCollection
{


    public function toArray($request)
    {


        return $this->collection->transform(function ($row, $key) {
                $seller_ids = explode(",", $row->seller_ids);
                $internal_id = null;
                $description = null;
                $quantity = $row->total_quantity;
                $unit_type_id = null;
                $presentations = $row->presentations;
                $unit_totals = [];
        
                $has_pipe = strpos($presentations, '|');
                if ($has_pipe !== false) {
                    $presentations = explode(",", $presentations);
                    $presentations = array_map(function($row) {
                        $row = explode("|", $row);
                        return [
                            "description" => isset($row[0]) ? $row[0] : null,
                            "unit_type_id" => isset($row[1]) ? $row[1] : null,
                            "unit_quantity" => isset($row[2]) ? $row[2] : 0,
                            "quantity" => isset($row[3]) ? $row[3] : 0,
                        ];
                    }, $presentations);
        
                    // Acumular las cantidades por tipo de unidad
                    foreach ($presentations as $presentation) {
                        $unit_type = $presentation['description'];
                        // $unit_quantity = (float) $presentation['unit_quantity'];
                        $quantity_ = (float) $presentation['quantity'];
        
                        if (!isset($unit_totals[$unit_type])) {
                            $unit_totals[$unit_type] = 0;
                        }
                        $unit_totals[$unit_type] += $quantity_;
                    }
                }
        
                // Formatear el resultado final
                $formatted_result = [];
                foreach ($unit_totals as $unit_type => $total_quantity) {
                    $formatted_result[] = "{$total_quantity} {$unit_type}";
                }
                $quantity_presentation = null;
                $total = null;
                $item = Item::find($row->item_id);
                if($item){
                    $internal_id = $item->internal_id;
                    $description = $item->description;
                
                    $unit_type_id = $item->unit_type_id;
                    $quantity_presentation = $row->presentations;
                    $total = $row->total;
                }
            return  [
                "seller_ids" => $seller_ids,
                "internal_id" => $internal_id,
                "description" => $description,
                "quantity" => $quantity,
                "unit_type_id" => $unit_type_id,
                "quantity_presentation" => $quantity_presentation,
                "total" => $total,
                'presentations' => implode(", ", $formatted_result),
            ];
        });
    }
}

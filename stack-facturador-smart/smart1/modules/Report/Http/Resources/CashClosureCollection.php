<?php

namespace Modules\Report\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CashClosureCollection extends ResourceCollection
{
     

    public function toArray($request) {
        

        return $this->collection->transform(function($row, $key){ 
             
 
               
            return [
                'id' => $row->id,
                'user_name' => $row->user->name,
                'user_id' => $row->user_id,
                'number_closures' => $row->number_closures,
                'total_balance' => number_format($row->total_balance, 2, '.', ''),
            ];
        });
    }
}

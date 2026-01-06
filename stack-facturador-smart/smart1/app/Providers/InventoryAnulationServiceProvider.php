<?php

namespace App\Providers;

use App\Models\Tenant\Document;  
use Illuminate\Support\ServiceProvider;
use App\Traits\InventoryKardexTrait;


class InventoryAnulationServiceProvider extends ServiceProvider
{

    use InventoryKardexTrait;
    
    public function register()
    {
    }
    
    public function boot()
    {
        // $this->anulation();
    }


    private function anulation(){

        Document::updated(function ($document) { 
            $original_state = $document->getOriginal('state_type_id');
            $current_state = $document->state_type_id;
            if($original_state == '11' && $current_state == '11') return;
            if($original_state == '09' && $current_state == '09') return;
            
            if(isset($document['no_stock']) && $document['no_stock'] == true) return;
            if($document['document_type_id'] == '01' || $document['document_type_id'] == '03'){

                if($document['state_type_id'] == 11){

                    foreach ($document['items'] as $detail) {      
                        
                        $this->updateStock($detail['item_id'], $document['establishment_id'], $detail['quantity'], false); 
                        $this->saveInventoryKardex($document, $detail['item_id'], $document['establishment_id'], -$detail['quantity']);
            
                    }

                }
            }         

            
        });
        
    }
}

<?php

namespace App\Providers;

use App\Models\Tenant\Item;
use App\Models\Tenant\Document;
use App\Models\Tenant\DocumentItem;
use App\Models\Tenant\Purchase;

use App\Models\Tenant\Kardex;
use App\Models\Tenant\Note;
use Illuminate\Support\ServiceProvider;
use App\Traits\KardexTrait;

class AnulationServiceProvider extends ServiceProvider
{
    use KardexTrait;

    public function register()
    {
    }

    public function boot()
    {
        $this->anulation();
        //$this->anulation_purchase();

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

                        // $item = Item::find($detail['item_id']);
                        // $item->stock = $item->stock + $detail['quantity'];
                        // $item->save();
                        
                        $this->updateStock($detail['item_id'], $detail['quantity'], false);
                        
                        $this->saveKardex('sale', $detail['item_id'], $document['id'], -$detail['quantity'],'document');

                    }

                }
            }
            if($document['document_type_id'] == '08' && $document['state_type_id'] == 11){
                $note = Note::where('document_id', $document['id'])->first();
                $affected_document_id = $note->affected_document_id;
                $document_affected = Document::find($affected_document_id);
                if($document_affected && $document_affected->payment_condition_id === '02'){
                    $document_affected->ajustDocumentFee();
                }
            }


        });

    }

    private function anulation_purchase(){

        Purchase::updated(function ($document) {

                if($document['state_type_id'] == 11){

                    foreach ($document['items'] as $detail) {

                        $this->updateStock($detail['item_id'], $detail['quantity'], true); //pongo true porque la compra se anula, entonces el stock disminuye

                       // $this->saveKardex('sale', $detail['item_id'], $document['id'], -$detail['quantity'],'document');

                    }

                }



        });

    }
}

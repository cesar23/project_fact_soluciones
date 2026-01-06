<?php

namespace Modules\Restaurant\Traits;

use Modules\Restaurant\Models\Orden;
use Modules\Restaurant\Models\OrdenItem;
use Modules\Restaurant\Models\Table;

trait OrderTrait
{
    private function finishOrder($orden_id)
    {
        $orden = Orden::find($orden_id);
        $orden->status_orden_id = 4;
        OrdenItem::where('orden_id', $orden_id)->update(['status_orden_id' => 4]);
        $orden->save();
        if ($orden->table_id) {
            $table = Table::find($orden->table_id);
            $table->status_table_id = 1;
            $table->save();
        }
    }

    private function setDocumentOrdenId($orden_id, $document_id)
    {
        $orden = Orden::find($orden_id);
        $orden->document_id = $document_id;
        $orden->save();
    }


    private function setSaleNoteId($orden_id, $sale_note_id)
    {
        $orden = Orden::find($orden_id);
        $orden->sale_note_id = $sale_note_id;
        $orden->save();
    }

    private function setCustomerId($orden_id, $customer_id)
    {
        $orden = Orden::find($orden_id);
        $orden->customer_id = $customer_id;
        $orden->save();
    }

    public function processOrder($orden_id, $sale_note_id, $customer_id, $is_sale_note = false)
    {
        $this->finishOrder($orden_id);
        $this->setCustomerId($orden_id, $customer_id);
        if ($is_sale_note) {
            $this->setSaleNoteId($orden_id, $sale_note_id);
        } else {
            $this->setDocumentOrdenId($orden_id, $sale_note_id);
        }
    }
}

<?php

namespace Modules\Seller\Traits;

use Modules\Seller\Models\RecordSellerSale;

/**
 *Se encarga de sumar las ventas de un vendedor en un mes y año determinado.
 */
trait SellerSaleTrait

{
    public function setSellerSale($sale, $update = false)
    {
        $user_id = $sale->seller_id;
        $date = $sale->date_of_issue;
        $total = $sale->total;
        $record =  RecordSellerSale::where('user_id', $user_id)
            ->whereMonth('date_of_record', date('m', strtotime($date)))
            ->whereYear('date_of_record', date('Y', strtotime($date)))
            ->first();
        if (!$record) {
            $date = date('Y-m-d', strtotime('first day of ' . $date));
            $record = RecordSellerSale::create([
                'user_id' => $user_id,
                'date_of_record' => $date,
                'total' => 0
            ]);
        }
        if ($sale->currency_type_id !== 'PEN') {
            $total = $sale->total * $sale->exchange_rate_sale;
        }
        if (isset($sale->document_type_id)) {
            if ($sale->document_type_id === '07') {
                $total = $total * -1;
            }
        }

        $state_type_id = $sale->state_type_id;
        if ($state_type_id === '11') {
            $total = $total * -1;
        }




        if ($update) {
            $data_original = $sale->getOriginal();
            $negative = ["09", "11", "13"];
            $state_type_id_original = $data_original['state_type_id'];
            $state_type_id = $sale->state_type_id;
            $positive = ["01", "03", "05"];

            if (in_array($state_type_id_original, $negative) && in_array($state_type_id, $negative)) {
                return;
            }


            $total_original = $data_original['total'];
            $document_type_id = isset($data_original['document_type_id']) ? $data_original['document_type_id'] : null;
            if (in_array($state_type_id_original, $positive)) {
                $total_original = $total_original * -1;
                if ($document_type_id) {
                    if ($document_type_id == '07') {
                        $total_original = $total_original * -1;
                    }
                }
            }

            $record->total += $total_original;
            $record->save();
            if (in_array($state_type_id, $positive)) {
                $record->total += $total;
            }
            $record->save();
        } else {
            $record->total += $total;
            $record->save();
        }
    }

    function restoreTotal($sale)
    {
        $original = $sale->getOriginal();
        $total = $sale['total'];
        $document_type_id = isset($original['document_type_id']) ? $original['document_type_id'] : null;
        $is_note_credit = $this->isNoteCredit($document_type_id);
    }

    function isNoteCredit($document_type_id)
    {
        return $document_type_id === '07';
    }
}

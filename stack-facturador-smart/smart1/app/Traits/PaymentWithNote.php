<?php

namespace App\Traits;

use App\Models\Tenant\DocumentPayment;
use App\Models\Tenant\Note;
use App\Models\Tenant\PaymentWithCreditNote;
use App\Models\Tenant\SaleNotePayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Expense\Models\ExpensePayment;

trait PaymentWithNote
{ 

    public function savePaymentWithNote($type, $payment_id, $note_id, $amount) {

        $property = null;
        if($type == 'sale_note'){
            $property = 'sale_note_payment_id';
        }elseif($type == 'document'){
            $property = 'document_payment_id';
        }elseif($type == 'expense'){
            $property = 'expense_payment_id';
        }

         PaymentWithCreditNote::create([
            $property => $payment_id,
            'note_id' => $note_id,
            'amount' => $amount
        ]);

        DB::connection('tenant')->table('notes')->where('id', $note_id)->update([
            'is_used' => true
        ]);



    }
 
    


}

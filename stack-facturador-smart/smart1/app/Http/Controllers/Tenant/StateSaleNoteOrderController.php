<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\BankAccount;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\SaleNoteOrderState;
use App\Models\Tenant\StateSaleNoteOrder;
use App\Models\Tenant\TelephonePerson;
use Illuminate\Http\Request;

class StateSaleNoteOrderController extends Controller
{

    public function recordsStates()
    {
        $state_sale_note_orders = StateSaleNoteOrder::all();
        $destinations = BankAccount::all()->transform(function ($item) {
            return [
                'id' => $item->id,
                'bank_id' => $item->bank_id,
                'description' => $item->description
            ];
        });
        $payment_methods = PaymentMethodType::all();
        return compact('state_sale_note_orders', 'destinations', 'payment_methods');
    }

    public function store(Request $request)
    {
        $state_sale_note_order_id = $request->input('state_sale_note_order_id');
        $sale_note_id = $request->input('sale_note_id');
        $state_sale_note_order = new SaleNoteOrderState();
        $state_sale_note_order->state_sale_note_order_id = $state_sale_note_order_id;
        $state_sale_note_order->sale_note_id = $sale_note_id;
        $state_sale_note_order->save();
        return [
            'success' => true,
            'message' => 'Estado de la orden guardado con éxito',
        ];
    }

    public function destroy($id)
    {
        $telephone_person = TelephonePerson::findOrFail($id);
        $telephone_person->delete();
        return [
            'success' => true,
            'message' => 'Telefono eliminado con éxito',
        ];
    }
}

<?php
namespace Modules\Purchase\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Purchase\Http\Resources\PurchasePaymentCollection;
use Modules\Purchase\Http\Requests\PurchasePaymentRequest;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\PurchasePayment;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\PurchaseFee;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Modules\Finance\Traits\FinanceTrait; 
use Modules\Finance\Traits\FilePaymentTrait; 
use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\PaymentFile;

class PurchasePaymentController extends Controller
{
    use FinanceTrait, FilePaymentTrait;

    public function records($purchase_id)
    {
        $records = PurchasePayment::where('purchase_id', $purchase_id)->get();

        return new PurchasePaymentCollection($records);
    }
    
    public function uploadFilePurchasePayment(Request $request)
    {
        $payment_id = $request->input('payment_id');
        $file = $request->file('file');
        $file_name = $file->getClientOriginalName();
        $file->storeAs('payment_files'.DIRECTORY_SEPARATOR.'purchases', $file_name, 'tenant');
        PaymentFile::create([
            'payment_id' => $payment_id,
            'filename' => $file_name,
            'payment_type' => PurchasePayment::class,
        ]);
        return [
            'success' => true,
            'message' => 'Archivo subido con éxito'
        ];

    }

    public function tables()
    {
        return [
            'payment_method_types' => PaymentMethodType::all(),
            'payment_destinations' => $this->getPaymentDestinations()
        ];
    }

    public function purchase($purchase_id)
    {
        $purchase = Purchase::find($purchase_id);

        $total_paid = collect($purchase->payments)->sum('payment');
        $total = $purchase->total;
        $total_difference = round($total - $total_paid, 2);

        return [
            'number_full' => $purchase->number_full,
            'total_paid' => $total_paid,
            'total' => $total,
            'total_difference' => $total_difference
        ];

    }



    public function store(PurchasePaymentRequest $request)
    {
        $id = $request->input('id');

         DB::connection('tenant')->transaction(function () use ($id, $request) {

            $record = PurchasePayment::firstOrNew(['id' => $id]);
            $record->fill($request->all());
            $record->payment = str_replace(',', '', $request->payment);
            $record->save();
            $this->createGlobalPayment($record, $request->all());
            $this->updatePurchaseFees($record->purchase_id);
            $this->saveFiles($record, $request, 'purchases');

        });

        return [
            'success' => true,
            'message' => ($id)?'Pago editado con éxito':'Pago registrado con éxito'
        ];
    }

    public function destroy($id)
    {
        $item = PurchasePayment::findOrFail($id);
        $item->delete();
        $this->updatePurchaseFees($item->purchase_id);

        return [
            'success' => true,
            'message' => 'Pago eliminado con éxito'
        ];
    }
 
    public function records_fee($purchase_fee_id)
    {

        $records = PurchasePayment::where('purchase_fee_id', $purchase_fee_id)->get();
        return new PurchasePaymentCollection($records);
    }
    public function purchase_fee($purchase_fee_id)
    {

        $isCredit = false;
        $fee = [];
        $purchase_fee = PurchaseFee::find($purchase_fee_id);
        $this->updatePurchaseFees($purchase_fee->purchase_id);
        $purchase_fee = PurchaseFee::find($purchase_fee_id);
        if ($purchase_fee->original_amount == null) {
            $purchase_fee->original_amount = $purchase_fee->amount;
            $purchase_fee->save();
        }
        $total = $purchase_fee->original_amount;

        $total_paid = $purchase_fee->original_amount - $purchase_fee->amount;


        $total_difference = round($total - $total_paid, 2);

        return [
            'number_full' => $purchase_fee->purchase->number_full,
            'total_paid' => $total_paid,
            'total' => $total,
            'total_difference' => $total_difference,
            'currency_type_id' => $purchase_fee->purchase->currency_type_id,
            'exchange_rate_sale' => (float) $purchase_fee->purchase->exchange_rate_sale,
            'external_id' => $purchase_fee->purchase->external_id,
            'fee' => $fee,
            'is_credit' => $isCredit,
        ];
    }
    public function updatePurchaseFees($purchase_id)
    {
        // Primero reseteamos todas las cuotas a su estado original
        PurchaseFee::where('purchase_id', $purchase_id)
            ->update([
                'original_amount' => DB::raw('CASE WHEN original_amount IS NULL OR original_amount = 0 THEN amount ELSE original_amount END'),
                'amount' => DB::raw('CASE WHEN original_amount IS NULL OR original_amount = 0 THEN amount ELSE original_amount END'),
                'is_canceled' => false
            ]);

        // Obtenemos los pagos y las cuotas ordenadas por fecha
        $total_paid = PurchasePayment::where('purchase_id', $purchase_id)->sum('payment');
        $fees = PurchaseFee::where('purchase_id', $purchase_id)
            ->orderBy('date', 'asc')
            ->get();

        $remaining_payment = $total_paid;

        foreach ($fees as $fee) {
            if ($remaining_payment <= 0) {
                break;
            }

            // Calculamos cuánto se puede pagar de esta cuota
            $amount_to_pay = min($remaining_payment, $fee->amount);

            // Actualizamos el monto restante de la cuota
            $new_amount = $fee->amount - $amount_to_pay;

            $fee->amount = $new_amount;
            $fee->is_canceled = ($new_amount <= 0);
            $fee->save();

            // Reducimos el pago restante
            $remaining_payment -= $amount_to_pay;
        }
    }
    public function store_fee(PurchasePaymentRequest $request)
    {
        $id = $request->input('purchase_fee_id');
        $purchase_fee = PurchaseFee::find($id);
        $purchase_id = $purchase_fee->purchase_id;
        $data =  DB::connection('tenant')->transaction(function () use ($id, $request, $purchase_id) {
            $record = PurchasePayment::firstOrNew(['id' => null]);
            $record->fill($request->all());
            $record->purchase_id = $purchase_id;
            $record->cash_id = User::getCashId();
            $record->purchase_fee_id = $id;
            $record->save();
            $this->createGlobalPayment($record, $request->all());
            $this->updatePurchaseFees($purchase_id);
            $this->saveFiles($record, $request, 'purchases');

            return $record;
        });




        return [
            'success' => true,
            'message' => ($id) ? 'Pago editado con éxito' : 'Pago registrado con éxito',
            'id' => $data->id,
        ];
    }


}

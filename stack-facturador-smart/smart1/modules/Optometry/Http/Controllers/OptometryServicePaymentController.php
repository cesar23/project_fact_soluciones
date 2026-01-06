<?php

namespace Modules\Optometry\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\Tenant\PaymentMethodType;
use Modules\Finance\Traits\FinanceTrait;
use Illuminate\Support\Facades\DB;
use Modules\Optometry\Http\Requests\OptometryServicePaymentRequest;
use Modules\Optometry\Http\Resources\OptometryServicePaymentCollection;
use Modules\Optometry\Models\OptometryService;
use Modules\Optometry\Models\OptometryServicePayment;

class OptometryServicePaymentController extends Controller
{

    use FinanceTrait;

    public function records($technical_service_id)
    {
        $records = OptometryServicePayment::where('optometry_service_id', $technical_service_id)->get();

        return new OptometryServicePaymentCollection($records);
    }

    public function tables()
    {
        return [
            'payment_method_types' => PaymentMethodType::all(),
            'payment_destinations' => $this->getPaymentDestinations()
        ];
    }


    public function document($technical_service_id)
    {
        $record = OptometryService::find($technical_service_id);

        $total_paid = round(collect($record->payments)->sum('payment'), 2);
        $total = $record->cost + $record->total;
        $total_difference = round($total - $total_paid, 2);

        return [
            'number_full' => $record->number_full,
            'total_paid' => $total_paid,
            'total' => $total,
            'total_difference' => $total_difference,
        ];
    }


    public function store(OptometryServicePaymentRequest $request)
    {
        $id = $request->input('id');

         DB::connection('tenant')->transaction(function () use ($id, $request) {

            $record = OptometryServicePayment::query()->firstOrNew(['id' => $id]);
            $record->fill($request->all());
            $record->save();
            $this->createGlobalPayment($record, $request->all());
        });

        return [
            'success' => true,
            'message' => ($id)?'Pago editado con éxito':'Pago registrado con éxito'
        ];
    }


    public function destroy($id)
    {
        $item = OptometryServicePayment::findOrFail($id);
        $item->delete();

        return [
            'success' => true,
            'message' => 'Pago eliminado con éxito'
        ];
    }



}

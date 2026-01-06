<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Sale\Http\Resources\PaymentMethodTypeCollection;
use App\Models\Tenant\PaymentMethodType;
use Exception;
use Modules\Sale\Http\Requests\PaymentMethodTypeRequest;
use App\Traits\CacheTrait;

class PaymentMethodTypeController extends Controller

{
    use CacheTrait;

    public function records(Request $request)
    {
        $records = PaymentMethodType::get();

        return new PaymentMethodTypeCollection($records);
    }

    public function changeShowInPos(Request $request)
    {
        $payment_method_type = PaymentMethodType::findOrFail($request->id);
        $payment_method_type->show_in_pos = $request->show_in_pos;
        $payment_method_type->save();

        return response()->json(['success' => true, 'message' => 'Metodo de pago actualizado correctamente']);
    }


    public function record($id)
    {
        $record = PaymentMethodType::findOrFail($id);

        return $record;
    }


    public function changeDestination(Request $request)
    {
        $destination_id = $request->input('destination_id');
        $id = $request->input('id');
        $record = PaymentMethodType::findOrFail($id);
        $record->destination_id = $destination_id;
        $record->save();
        CacheTrait::clearCache('cash_payment_methods');
        CacheTrait::clearCache('credit_payment_methods');
        return response()->json(['success' => true, 'message' => 'Destino actualizado correctamente']);
    }
    public function change_type(Request $request)
    {

        $type = $request->input('type');
        $id = $request->input('id');
        $record = PaymentMethodType::findOrFail($id);
        $record->is_digital = false;
        $record->is_bank = false;
        $record->is_credit = false;
        $record->is_cash = false;
        $record->$type = true;
        $record->save();
        CacheTrait::clearCache('cash_payment_methods');
        CacheTrait::clearCache('credit_payment_methods');
        return [
            'success' => true,
            'message' => 'Método de pago editado con éxito'
        ];
    }
    public function store(PaymentMethodTypeRequest $request)
    {

        $id = $request->input('id');
        $type = $request->input('condition_payment');
        $record = PaymentMethodType::firstOrNew(['id' => $id]);
        $record->fill($request->all());
        $record->is_digital = false;
        $record->is_bank = false;
        $record->is_credit = false;
        $record->is_cash = false;
        $record->$type = true;
        $record->save();
        CacheTrait::clearCache('cash_payment_methods');
        CacheTrait::clearCache('credit_payment_methods');

        return [
            'success' => true,
            'message' => ($id) ? 'Método de pago editado con éxito' : 'Método de pago registrado con éxito',
        ];
    }

    public function destroy($id)
    {
        try {

            $record = PaymentMethodType::findOrFail($id);
            $record->delete();

            return [
                'success' => true,
                'message' => 'Método de pago eliminado con éxito'
            ];
        } catch (Exception $e) {

            return ($e->getCode() == '23000') ? ['success' => false, 'message' => "El Método de pago esta siendo usado por otros registros, no puede eliminar"] : ['success' => false, 'message' => "Error inesperado, no se pudo eliminar el Método de pago "];
        }
    }
}

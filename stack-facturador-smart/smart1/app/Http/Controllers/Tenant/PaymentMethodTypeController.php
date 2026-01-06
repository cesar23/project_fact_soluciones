<?php
namespace App\Http\Controllers\Tenant;

use App\Models\Tenant\Catalogs\PaymentMethodType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\PaymentMethodTypeRequest;
use App\Http\Resources\Tenant\PaymentMethodTypeCollection;
use App\Http\Resources\Tenant\PaymentMethodTypeResource;
use App\Models\Tenant\BankAccount;
use App\Models\Tenant\PaymentMethodType as TenantPaymentMethodType;
use App\Traits\CacheTrait;
use Exception;
use Illuminate\Http\Request;

class PaymentMethodTypeController extends Controller
{
    use CacheTrait;
    public function records()
    {
        $records = PaymentMethodType::all();
        CacheTrait::clearCache('cash_payment_methods');
        CacheTrait::clearCache('credit_payment_methods');
        return new PaymentMethodTypeCollection($records);
    }

    public function destinations()
    {
        $bank_account = BankAccount::where('status', 1)->get()->map(function($item){
            return [
                'value' => strval($item->id),
                'label' => $item->description
            ];
        });
        return response()->json(['data' => $bank_account]);
    }


    public function changeShowInPos(Request $request)
    {
        $payment_method_type = TenantPaymentMethodType::findOrFail($request->id);
        $payment_method_type->show_in_pos = $request->show_in_pos;
        $payment_method_type->save();
        CacheTrait::clearCache('cash_payment_methods');
        CacheTrait::clearCache('credit_payment_methods');
        return response()->json(['success' => true, 'message' => 'Metodo de pago actualizado correctamente']);
    }

    public function record($id)
    {
        $record = new PaymentMethodTypeResource(PaymentMethodType::findOrFail($id));
        return $record;
    }

    public function store(PaymentMethodTypeRequest $request)
    {
        $id = $request->input('id');
        $unit_type = PaymentMethodType::firstOrNew(['id' => $id]);
        $unit_type->fill($request->all());
        $unit_type->save();
        CacheTrait::clearCache('cash_payment_methods');
        CacheTrait::clearCache('credit_payment_methods');
        return [
            'success' => true,
            'message' => ($id)?'Método de pago editada con éxito':'Método de pago registrada con éxito'
        ];
    }

    public function destroy($id)
    {
        try {

            $record = PaymentMethodType::findOrFail($id);
            $record->delete();

            return [
                'success' => true,
                'message' => 'Método de pago eliminada con éxito'
            ];

        } catch (Exception $e) {

            return ($e->getCode() == '23000') ? ['success' => false,'message' => 'El método de pago esta siendo usada por otros registros, no puede eliminar'] : ['success' => false,'message' => 'Error inesperado, no se pudo eliminar la unidad'];

        }


    }

}

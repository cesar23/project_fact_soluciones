<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ConditionBlockPaymentMethod;
use App\Traits\CacheTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConditionBlockPaymentMethodController extends Controller
{
    use CacheTrait;
    public function records()
    {
        $records = ConditionBlockPaymentMethod::all();

        return $records;
    }

    public function record($id)
    {
        $record = ConditionBlockPaymentMethod::findOrFail($id);

        return $record;
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            CacheTrait::clearCache('cash_payment_methods');
            CacheTrait::clearCache('credit_payment_methods');
            $cash_payment_methods = $request->cash_payment_methods;
            $credit_payment_methods = $request->credit_payment_methods;
            $payment_method_types = [];
            foreach ($cash_payment_methods as $payment_method_type) {
                $payment_method_types[] = [
                    'payment_condition_id' => '01',
                    'payment_method_type' => $payment_method_type,
                ];
            }
            foreach ($credit_payment_methods as $payment_method_type) {
                $payment_method_types[] = [
                    'payment_condition_id' => '02',
                    'payment_method_type' => $payment_method_type,
                ];
            }
            DB::connection('tenant')->table('condition_block_payment_methods')->truncate();
            foreach ($payment_method_types as $payment_method_type) {
                DB::connection('tenant')->table('condition_block_payment_methods')->insert([
                    'payment_condition_id' => $payment_method_type['payment_condition_id'],
                    'payment_method_type' => $payment_method_type['payment_method_type'],
                ]);
            }
            DB::commit();
            return response()->json(['message' => 'Métodos de pago establecidos correctamente'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

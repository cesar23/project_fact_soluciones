<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\ExchangeCurrencyCollection;
use App\Http\Resources\Tenant\ExchangeCurrencyResource;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\ExchangeCurrency;
use App\Models\Tenant\Item;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExchangeCurrencyController extends Controller
{


    public function index()
    {
        return view('tenant.exchange_currency.index');
    }

    public function tables()
    {
        $currency_types = CurrencyType::all();
        $currencies = $currency_types->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => $row->description,
                'symbol' => $row->symbol,
            ];
        });









        return compact('currencies');
    }

    public function records(Request $request)
    {
        $date = $request->input('date');
        $currency_id = $request->input('currency_id');

        $records = ExchangeCurrency::query();

        if ($date) {
            $records = $records->where('date', $date);
        }

        if ($currency_id) {
            $records = $records->where('currency_id', $currency_id);
        }

        return new ExchangeCurrencyCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function exchange_date($date, $currency_id)
    {
        $exchange_rate   = ExchangeCurrency::where('currency_id', $currency_id)->where('date', $date)->first();
        if ($exchange_rate) {
            return ['success' => true, 'id' => $exchange_rate->id, 'date' => $exchange_rate->date,  'sale' => $exchange_rate->sale, 'purchase' => $exchange_rate->purchase];
        } else {
            return ['success' => false, 'message' => 'No se encontró el tipo de cambio para la fecha seleccionada', 'sale' => 1, 'purchase' => 1];
        }
    }
    public function record($id)
    {
        $record = new ExchangeCurrencyResource(ExchangeCurrency::findOrFail($id));

        return $record;
    }
    private function updateItemsPricesWithExchangeRateSale($exchange_rate_sale)
    {
        $profit_margin = (float) (Configuration::select('profit_margin')->value('profit_margin') ?? 0);
        $multiplier = 1 + ($profit_margin / 100);
        $exchange_rate_sale = (float) $exchange_rate_sale;

        $usd_factor = number_format($multiplier, 10, '.', '');
        $non_usd_factor = number_format($exchange_rate_sale * $multiplier, 10, '.', '');

        Item::query()
            ->where('active', true)
            ->where('currency_type_id', 'USD')
            ->where('purchase_unit_price', '>', 0)
            ->update([
                'sale_unit_price' => DB::raw('purchase_unit_price * ' . $usd_factor),
            ]);

        Item::query()
            ->where('active', true)
            ->where('currency_type_id', '!=', 'USD')
            ->where('purchase_unit_price', '>', 0)
            ->update([
                'sale_unit_price' => DB::raw('purchase_unit_price * ' . $non_usd_factor),
            ]);
    }

    public function store(Request $request)
    {
        try {
            DB::connection('tenant')->beginTransaction();
            $id = $request->input('id');
            $currency_id = $request->input('currency_id');
            $date = $request->input('date');
            $now = now()->format('Y-m-d');
            if (!$id) {
                $exist = ExchangeCurrency::where('currency_id', $currency_id)->where('date', $date)->first();
                if ($exist) {
                    return [
                        'success' => false,
                        'message' => 'Ya existe un tipo de cambio para la fecha seleccionada'
                    ];
                }
            }
            $currency_type = ExchangeCurrency::firstOrNew(['id' => $id]);
            $currency_type->fill($request->all());

            $currency_type->save();
            $updated = false;
            if ($date == $now && $currency_type->currency_id == 'USD') {
                // $this->updateItemsPricesWithExchangeRateSale($currency_type->sale);
                $updated = true;
            }

            DB::connection('tenant')->commit();
            $message = ($id) ? 'Tipo de cambio editado con éxito' : 'Tipo de cambio registrado con éxito';
            if ($updated) {
                $message .= ' y se actualizaron los precios de los productos con el tipo de cambio y el margen de ganancia por producto.';
            }
            return [
                'success' => true,
                'message' => $message
            ];
        } catch (Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error($e->getMessage() . ' ' . $e->getLine());
            return [
                'success' => false,
                'message' => 'Error al registrar el tipo de cambio'
            ];
        }
    }

    public function destroy($id)
    {


        $currency_type = ExchangeCurrency::findOrFail($id);
        $currency_type->delete();

        return [
            'success' => true,
            'message' => 'Tipo de cambio eliminado con éxito'
        ];
    }
}

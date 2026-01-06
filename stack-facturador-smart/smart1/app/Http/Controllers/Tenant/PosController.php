<?php

namespace App\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Item;
use App\Models\Tenant\Person;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Series;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\CardBrand;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\User;
use Modules\Inventory\Models\Warehouse;
use App\Models\Tenant\Cash;
use App\Models\Tenant\Configuration;
use Modules\Inventory\Models\InventoryConfiguration;
use Modules\Inventory\Models\ItemWarehouse;
use Exception;
use Modules\Item\Models\Category;
use Modules\Finance\Traits\FinanceTrait;
use App\Models\Tenant\Company;
use Modules\BusinessTurn\Models\BusinessTurn;
use App\Http\Resources\Tenant\PosCollection;
use App\Models\Tenant\BankAccount;
use App\Models\Tenant\Catalogs\{
    ChargeDiscountType,
    UnitType
};
use App\Models\Tenant\ConditionBlockPaymentMethod;
use App\Models\Tenant\TelephonePerson;
use App\Models\Tenant\UserDefaultDocumentType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Item\Models\Brand;
use PSpell\Config;

class PosController extends Controller
{

    use FinanceTrait;
    public function customerById($customer_id)
    {
        $customer = Person::find($customer_id);
        $customer_transform = [
            'id' => $customer->id,
            'description' => $customer->number . ' - ' . $customer->name,
            'name' => $customer->name,
            'number' => $customer->number,
            'identity_document_type_id' => $customer->identity_document_type_id,
            'identity_document_type_code' => $customer->identity_document_type->code,
        ];

        return response()->json($customer_transform);
    }
    public function last_sale()
    {
        $last_document = \App\Models\Tenant\Document::latest()->first();
        $last_sale_note = \App\Models\Tenant\SaleNote::latest()->first();

        if (!$last_document && !$last_sale_note) {
            return response()->json(null);
        }

        $latest = collect([
            ['type' => 'document', 'data' => $last_document],
            ['type' => 'sale_note', 'data' => $last_sale_note]
        ])
            ->filter(function ($item) {
                return !is_null($item['data']);
            })
            ->sortByDesc(function ($item) {
                return $item['data']->created_at;
            })
            ->first();
        $external_id = $latest['data']->external_id;
        $url = null;
        if ($latest['type'] == 'document') {
            $url = url('') . "/print/document/{$external_id}/ticket";
        } else {
            $url = url('') . "/sale-notes/print/{$external_id}/ticket";
        }
        return response()->json($url);
    }
    public function index()
    {

        $cash = Cash::where([['user_id', User::getUserCashId()], ['state', true]])->first();
        $is_super_admin = auth()->user()->type == 'superadmin';
        if ( $is_super_admin) {
            $cash = true;
        }
        if (!$cash) return redirect()->route('tenant.cash.index');

        $configuration = Configuration::first();
        if ($configuration->pos_quick_sale) {
            Item::checkIfExistBaseItem();
        }
        $is_food_dealer = BusinessTurn::isFoodDealer();
        $company = Company::select('soap_type_id')->first();
        $soap_company  = $company->soap_type_id;
        $business_turns = BusinessTurn::select('active')->where('id', 4)->first();
        $configuration = Configuration::first();
        if ($configuration->show_pos_lite) {
            return view('tenant.pos.index_lite', compact('is_super_admin','configuration', 'soap_company', 'business_turns', 'is_food_dealer'));
        }else if($configuration->show_pos_lite_v2){
            return view('tenant.pos.index_lite_v2', compact('is_super_admin','configuration', 'soap_company', 'business_turns', 'is_food_dealer'));
        } else {
            return view('tenant.pos.index', compact('is_super_admin','configuration', 'soap_company', 'business_turns', 'is_food_dealer'));
        }
    }

    public function index_full()
    {
        $cash = Cash::where([['user_id', User::getUserCashId()], ['state', true]])->first();

        if (!$cash) return redirect()->route('tenant.cash.index');

        return view('tenant.pos.index_full');
    }

    public function search_items(Request $request)
    {
        $configuration =  Configuration::first();
        $search_item_by_barcode_presentation = $request->search_item_by_barcode_presentation == 'true';
        $items_query = Item::
        ItemIsNotInput()->
        where('description', 'like', '%' . str_replace(' ', '%', $request->input_item) . '%')
            ->orWhere('name', 'like',  '%' . str_replace(' ', '%', $request->input_item) . '%')
            ->orWhere(function ($query) use ($request) {
                $query->where('internal_id', 'like', "%{$request->input_item}%")
                    ->orWhere('barcode', "{$request->input_item}");
            })
            ->orWhereHas('category', function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->input_item . '%');
            })
            ->orWhereHas('brand', function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->input_item . '%');
            })
            ->whereWarehouse();


        if ($search_item_by_barcode_presentation) $items_query->orFilterItemUnitTypeBarcode($request->input_item);

        $items =  $items_query->whereIsActive()->limit(20)->get()->transform(function ($row) use ($configuration, $search_item_by_barcode_presentation, $request) {

            $full_description = ($row->internal_id) ? $row->internal_id . ' - ' . $row->description : $row->description;
            $configuration = Configuration::first();
            $sale_unit_price = $this->getSaleUnitPrice($row, $configuration);
            return [
                'id' => $row->id,
                'item_id' => $row->id,
                'payment_conditions' => $row->payment_conditions,
                'sizes' => $row->sizes,
                'full_description' => $full_description,
                'description' => ($row->brand->name) ? $row->description . ' - ' . $row->brand->name : $row->description,
                'currency_type_id' => $row->currency_type_id,
                'internal_id' => $row->internal_id,
                'currency_type_symbol' => $row->currency_type->symbol,
                'sale_unit_price' => number_format($sale_unit_price, $configuration->decimal_quantity, ".", ""),
                'purchase_unit_price' => $row->purchase_unit_price,
                'unit_type_id' => $row->unit_type_id,
                'unit_type_symbol' => optional($row->unit_type)->symbol ?? $row->unit_type_id,
                'aux_unit_type_id' => $row->unit_type_id,
                'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                'calculate_quantity' => (bool) $row->calculate_quantity,
                'is_set' => (bool) $row->is_set,
                'edit_unit_price' => false,
                'can_edit_price' => (bool) $row->can_edit_price,
                'has_igv' => (bool) $row->has_igv,
                'aux_quantity' => 1,
                'stock' => $row->getStockByWarehouse(),
                'aux_sale_unit_price' => number_format($row->sale_unit_price, $configuration->decimal_quantity, ".", ""),
                'edit_sale_unit_price' => number_format($row->sale_unit_price, $configuration->decimal_quantity, ".", ""),
                'image_url' => ($row->image !== 'imagen-no-disponible.jpg') ? asset('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR . $row->image) : asset("/logo/{$row->image}"),
                'sets' => collect($row->sets)->transform(function ($r) {
                    return [
                        $r->individual_item->description
                    ];
                }),
                'warehouses' => collect($row->warehouses)->transform(function ($row) {
                    return [
                        'warehouse_id' => $row->warehouse->id,
                        'warehouse_description' => $row->warehouse->description,
                        'stock' => $row->stock,
                    ];
                }),
                'unit_type' => $row->getItemUnitTypesBarcode($search_item_by_barcode_presentation, $request->input_item),
                // 'unit_type' => $row->item_unit_types,
                'category' => ($row->category) ? $row->category->name : null,
                'brand' => ($row->brand) ? $row->brand->name : null,
                'has_plastic_bag_taxes' => (bool) $row->has_plastic_bag_taxes,
                'amount_plastic_bag_taxes' => $row->amount_plastic_bag_taxes,
                'lots_enabled' => (bool) $row->lots_enabled,
                'lots_group' => collect($row->lots_group)->transform(function ($row) {
                    return [
                        'id' => $row->id,
                        'code' => $row->code,
                        'quantity' => $row->quantity,
                        'date_of_due' => $row->date_of_due,
                        'checked' => false,
                        'compromise_quantity' => 0,
                        'warehouse_id' => $row->warehouse_id,
                        'warehouse' => $row->warehouse_id ? $row->warehouse->description : null,

                    ];
                }),
                'has_isc' => (bool)$row->has_isc,
                'system_isc_type_id' => $row->system_isc_type_id,
                'percentage_isc' => $row->percentage_isc,
                'search_item_by_barcode_presentation' => $search_item_by_barcode_presentation,
                'series_enabled' => (bool) $row->series_enabled,
                'exchange_points' => $row->exchange_points,
                'quantity_of_points' => $row->quantity_of_points,
                'exchanged_for_points' => false, //para determinar si desea canjear el producto
                'used_points_for_exchange' => null, //total de puntos
                'original_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                'restrict_sale_cpe' => $row->restrict_sale_cpe,
                // 'name_product_pdf' =>($row->brand->name) ? $row->description . ' - ' . $row->brand->name : $row->description,

            ];
        });

        return compact('items');
    }
    private function getSaleUnitPrice($row, $configuration)
    {

        $sale_unit_price = number_format($row->sale_unit_price, $configuration->decimal_quantity, ".", "");

        if ($configuration->active_warehouse_prices) {

            $warehouse_price = $row->warehousePrices()->where('warehouse_id', auth()->user()->establishment->warehouse->id)->first();
            if ($warehouse_price) {
                $sale_unit_price = number_format($warehouse_price->price, $configuration->decimal_quantity, ".", "");
            } else {
                if ($row->warehousePrices()->count() > 0) {
                    $sale_unit_price = number_format($row->warehousePrices()->first()->price, $configuration->decimal_quantity, ".", "");
                }
            }
        }
        return $sale_unit_price;
    }
    function get_items_food_dealer()
    {
        $configuration =  Configuration::first();
        $items_query = Item::whereHas('food_dealer')
            ->whereWarehouse();



        $items =  $items_query->whereIsActive()->get()->transform(function ($row) {

            $full_description = ($row->internal_id) ? $row->internal_id . ' - ' . $row->description : $row->description;
            $configuration = Configuration::first();
            $sale_unit_price = $this->getSaleUnitPrice($row, $configuration);

            return [
                'id' => $row->id,
                'payment_conditions' => $row->payment_conditions,
                'start' => $row->food_dealer->start_time,
                'end' => $row->food_dealer->end_time,
                'item_id' => $row->id,
                'sizes' => $row->sizes,
                'full_description' => $full_description,
                'description' => ($row->brand->name) ? $row->description . ' - ' . $row->brand->name : $row->description,
                'currency_type_id' => $row->currency_type_id,
                'internal_id' => $row->internal_id,
                'currency_type_symbol' => $row->currency_type->symbol,
                'sale_unit_price' => number_format($sale_unit_price, $configuration->decimal_quantity, ".", ""),
                'purchase_unit_price' => $row->purchase_unit_price,
                'unit_type_id' => $row->unit_type_id,
                'unit_type_symbol' => optional($row->unit_type)->symbol ?? $row->unit_type_id,
                'aux_unit_type_id' => $row->unit_type_id,
                'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                'calculate_quantity' => (bool) $row->calculate_quantity,
                'is_set' => (bool) $row->is_set,
                'edit_unit_price' => false,
                'can_edit_price' => (bool) $row->can_edit_price,
                'has_igv' => (bool) $row->has_igv,
                'aux_quantity' => 1,
                'aux_sale_unit_price' => number_format($row->sale_unit_price, $configuration->decimal_quantity, ".", ""),
                'edit_sale_unit_price' => number_format($row->sale_unit_price, $configuration->decimal_quantity, ".", ""),
                'image_url' => ($row->image !== 'imagen-no-disponible.jpg') ? asset('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR . $row->image) : asset("/logo/{$row->image}"),
                'sets' => collect($row->sets)->transform(function ($r) {
                    return [
                        $r->individual_item->description
                    ];
                }),
                'warehouses' => collect($row->warehouses)->transform(function ($row) {
                    return [
                        'warehouse_description' => $row->warehouse->description,
                        'stock' => $row->stock,
                    ];
                }),
                'unit_type' => $row->item_unit_types,
                // 'unit_type' => $row->item_unit_types,
                'category' => ($row->category) ? $row->category->name : null,
                'brand' => ($row->brand) ? $row->brand->name : null,
                'has_plastic_bag_taxes' => (bool) $row->has_plastic_bag_taxes,
                'amount_plastic_bag_taxes' => $row->amount_plastic_bag_taxes,

                'has_isc' => (bool)$row->has_isc,
                'system_isc_type_id' => $row->system_isc_type_id,
                'percentage_isc' => $row->percentage_isc,
                'search_item_by_barcode_presentation' => false,

                'exchange_points' => $row->exchange_points,
                'quantity_of_points' => $row->quantity_of_points,
                'exchanged_for_points' => false, //para determinar si desea canjear el producto
                'used_points_for_exchange' => null, //total de puntos
                'original_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                'restrict_sale_cpe' => $row->restrict_sale_cpe,
            ];
        });

        // return compact('items');
        return $items;
    }
    public function tables()
    {

        $configuration = Configuration::getConfig();
        $series = [];
        $payment_method_types = [];
        $warehouses = DB::connection('tenant')->table('warehouses')->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => $row->description,
            ];
        });
        $cards_brand = [];
        $payment_destinations = [];
        $global_discount_types = [];
        $user_default_document = [];
        if ($configuration->pos_direct || $configuration->show_pos_lite || $configuration->show_pos_lite_v2)  {
            $series = Series::whereIn('document_type_id', ['01', '03', '80', 'PD', 'COT'])
                ->where([['establishment_id', auth()->user()->establishment_id], ['contingency', false]])
                ->get();
            $payment_method_types = ConditionBlockPaymentMethod::getCashPaymentMethods();
            $cards_brand = CardBrand::all();
            $payment_destinations = $this->getPaymentDestinations();
            $global_discount_types = ChargeDiscountType::whereIn('id', ['02', '03'])->whereActive()->get();
            $user = User::findOrFail(auth()->user()->id);
            if ($user->multiple_default_document_types) {
                $user_default_document = UserDefaultDocumentType::where('user_id', auth()->user()->id)
                    ->get()
                    ->map(function ($item) {
                        return ['document_id' => $item->document_type_id, 'series_id' => $item->series_id];
                    })
                    ->toArray();
            } else {
                $user_default_document = User::select(['document_id', 'series_id'])
                    ->where('id', auth()->user()->id)
                    ->get()
                    ->map(function ($item) {
                        return ['document_id' => $item->document_id, 'series_id' => $item->series_id];
                    })
                    ->toArray();
            }
        }
        $company = Company::active();
        $affectation_igv_types = AffectationIgvType::whereActive()->get();
        $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
        $currency_types = CurrencyType::whereActive()->get();
        $sellers = $this->getSellers();
        $customers = $this->table('customers');
        $suppliers = $this->table('suppliers');
        $configuration = Configuration::all();
        $items_food_dealer = [];
        if (BusinessTurn::isFoodDealer()) {
            $items_food_dealer = $this->get_items_food_dealer();
        }
        $user = User::findOrFail(auth()->user()->id);
        $customer_id = $establishment->customer_id;
        $customer_default = null;
        if( $customer_id ){
            $customer_db = DB::connection('tenant')->table('persons')->select('id', 'name', 'number')->where('id', $customer_id)->first();
            if($customer_db ){
                $customer_default = [
                    'id' => $customer_db->id,
                    'description' => $customer_db->number . ' - ' . $customer_db->name,
                ];
            }
        }
        // $items = $this->table('items');
        $items = [];

        $categories = Category::all();
        $unit_types = UnitType::all();
        return compact(
            'customer_default',
            'warehouses',
            'user_default_document',
            'series',
            'payment_method_types',
            'cards_brand',
            'payment_destinations',
            'global_discount_types',
            'unit_types',
            'items_food_dealer',
            'company',
            'configuration',
            'sellers',
            'items',
            'customers',
            'affectation_igv_types',
            'establishment',
            'user',
            'currency_types',
            'categories',
            'suppliers'
        );
    }
    public function tables_to_payment()
    {
        $payment_method_types = ConditionBlockPaymentMethod::getCashPaymentMethods()->transform(function ($row) {
            return [
                'id' => $row->id,
                'show_in_pos' => $row->show_in_pos,
                'description' => $row->description,
                'has_card' => $row->has_card,
                'charge' => $row->charge,
                'number_days' => $row->number_days,
                'is_credit' => $row->is_credit ? 1 : 0,
                'is_cash' => $row->is_cash ? 1 : 0,
                'is_digital' => $row->is_digital ? 1 : 0,
                'is_bank' => $row->is_bank ? 1 : 0,
            ];
        });
        $payment_destinations = $this->getPaymentDestinations();

        return compact('payment_method_types', 'payment_destinations');
    }
    public function api_payment_tables()
    {
        $series = Series::whereIn('document_type_id', ['01', '03', '80', 'PD', 'COT'])
            ->where([['establishment_id', auth()->user()->establishment_id], ['contingency', false]])
            ->get();

        // $payment_method_types = PaymentMethodType::NonCredit()->get();
        $payment_method_types = ConditionBlockPaymentMethod::getCashPaymentMethods()->transform(function ($row) {
            return [
                'id' => $row->id,
                'show_in_pos' => $row->show_in_pos,
                'description' => $row->description,
                'has_card' => $row->has_card,
                'charge' => $row->charge,
                'number_days' => $row->number_days,
                'is_credit' => $row->is_credit ? 1 : 0,
                'is_cash' => $row->is_cash ? 1 : 0,
                'is_digital' => $row->is_digital ? 1 : 0,
                'is_bank' => $row->is_bank ? 1 : 0,
            ];
        });
        // $payment_method_types_credit = PaymentMethodType::Credit()->get();
        $payment_method_types_credit = ConditionBlockPaymentMethod::getCreditPaymentMethods()->transform(function ($row) {
            return [
                'id' => $row->id,
                'show_in_pos' => $row->show_in_pos,
                'description' => $row->description,
                'has_card' => $row->has_card,
                'charge' => $row->charge,
                'number_days' => $row->number_days,
                'is_credit' => $row->is_credit ? 1 : 0,
                'is_cash' => $row->is_cash ? 1 : 0,
                'is_digital' => $row->is_digital ? 1 : 0,
                'is_bank' => $row->is_bank ? 1 : 0,
            ];
        });
        $cards_brand = CardBrand::all();
        $payment_destinations = $this->getPaymentDestinations();
        $global_discount_types = ChargeDiscountType::whereIn('id', ['02', '03'])->whereActive()->get();
        $user = User::findOrFail(auth()->user()->id);
        if ($user->multiple_default_document_types) {
            $user_default_document = UserDefaultDocumentType::where('user_id', auth()->user()->id)
                ->get()
                ->map(function ($item) {
                    return ['document_id' => $item->document_type_id, 'series_id' => $item->series_id];
                })
                ->toArray();
        } else {
            $user_default_document = User::select(['document_id', 'series_id'])
                ->where('id', auth()->user()->id)
                ->get()
                ->map(function ($item) {
                    return ['document_id' => $item->document_id, 'series_id' => $item->series_id];
                })
                ->toArray();
        }

        $payment_and_destination = $this->getPaymentAndDestination();

        return compact(
            'payment_and_destination',
            'series',
            'payment_method_types_credit',
            'payment_method_types',
            'cards_brand',
            'payment_destinations',
            'global_discount_types',
            'user_default_document'
        );
    }
    public function payment_tables()
    {
        $series = Series::whereIn('document_type_id', ['01', '03', '80', 'PD', 'COT'])
            ->where([['establishment_id', auth()->user()->establishment_id], ['contingency', false]])
            ->get();

        // $payment_method_types = PaymentMethodType::NonCredit()->get();
        $payment_method_types = ConditionBlockPaymentMethod::getCashPaymentMethods();
        // $payment_method_types_credit = PaymentMethodType::Credit()->get();
        $payment_method_types_credit = ConditionBlockPaymentMethod::getCreditPaymentMethods();
        $cards_brand = CardBrand::all();
        $payment_destinations = $this->getPaymentDestinations();
        $global_discount_types = ChargeDiscountType::whereIn('id', ['02', '03'])->whereActive()->get();
        $user = User::findOrFail(auth()->user()->id);
        if ($user->multiple_default_document_types) {
            $user_default_document = UserDefaultDocumentType::where('user_id', auth()->user()->id)
                ->get()
                ->map(function ($item) {
                    return ['document_id' => $item->document_type_id, 'series_id' => $item->series_id];
                })
                ->toArray();
        } else {
            $user_default_document = User::select(['document_id', 'series_id'])
                ->where('id', auth()->user()->id)
                ->get()
                ->map(function ($item) {
                    return ['document_id' => $item->document_id, 'series_id' => $item->series_id];
                })
                ->toArray();
        }

        $payment_and_destination = $this->getPaymentAndDestination();

        return compact(
            'payment_and_destination',
            'series',
            'payment_method_types_credit',
            'payment_method_types',
            'cards_brand',
            'payment_destinations',
            'global_discount_types',
            'user_default_document'
        );
    }

    public function getPaymentAndDestination()
    {
        $configuration = Configuration::first();
        if (!$configuration->list_payments_pos) {
            return [];
        }

        $payment_method_types = PaymentMethodType::where('show_in_pos', 1)->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => $row->description,
                'is_destination' => false,
                'has_operation_number' => $row->is_bank || $row->is_digital

            ];
        });

        $payment_destinations = BankAccount::where('show_in_pos', 1)->get()->transform(function ($row) {
            return [
                'id' => $row->id,
                'description' => $row->description,
                'is_destination' => true,
                'has_operation_number' => true
            ];
        });

        $payment_and_destination = array_merge($payment_method_types->toArray(), $payment_destinations->toArray());

        return $payment_and_destination;
    }

    public function table($table)
    {
        $is_from_api = request()->is('api/*');
        if ($table === 'customers') {

            // $identity_document_types =[];
            // $identity_document_types = DB::connection('tenant')->table('cat_identity_document_types')->pluck('code', 'id');
            $customers = Person::with('telephones:id,person_id,telephone')->whereType('customers')->whereIsEnabled()->orderBy('name');
            if (!$is_from_api) {
                $customers = $customers->take(20);
            }
            $customers = $customers->get();
            $customers_id = $customers->pluck('id')->toArray();
            $telephones = TelephonePerson::whereIn('person_id', $customers_id)->get();
            
            $customers = $customers->transform(function ($row) use ($telephones) {
                $telephone = ($row->telephone) ? $row->telephone : '';
                // $telephones = TelephonePerson::where('person_id', $row->id)->pluck('telephone')->toArray();
                $telephones = $telephones->where('person_id', $row->id)->pluck('telephone')->toArray();
                if ($telephone) {
                    array_push($telephones, $telephone);
                }
                return [
                    'telephones' => $telephones,
                    'id' => $row->id,
                    'barcode' => $row->barcode,
                    'description' => $row->number . ' - ' . $row->name,
                    'name' => $row->name,
                    'number' => $row->number,
                    'identity_document_type_id' => $row->identity_document_type_id,
                    'identity_document_type_code' => $row->identity_document_type->code,
                    'has_discount' => $row->has_discount,
                    'discount_type' => $row->discount_type,
                    'discount_amount' => $row->discount_amount,
                ];
            });
            return $customers;
        }
        if ($table === 'suppliers') {
            $suppliers = Person::whereType('suppliers')->whereIsEnabled()->orderBy('name')->get();
            $suppliers_id = $suppliers->pluck('id')->toArray();
            $telephones = TelephonePerson::whereIn('person_id', $suppliers_id)->get();
            $suppliers = $suppliers->transform(function ($row) use ($telephones) {
                $telephone = ($row->telephone) ? $row->telephone : '';
                $telephones = $telephones->where('person_id', $row->id)->pluck('telephone')->toArray();
                if ($telephone) {
                    array_push($telephones, $telephone);
                }
                return [
                    'telephones' => $telephones,
                    'id' => $row->id,
                    'barcode' => $row->barcode,
                    'description' => $row->number . ' - ' . $row->name,
                    'name' => $row->name,
                    'number' => $row->number,
                    'identity_document_type_id' => $row->identity_document_type_id,
                    'identity_document_type_code' => $row->identity_document_type->code,
                    'has_discount' => $row->has_discount,
                    'discount_type' => $row->discount_type,
                    'discount_amount' => $row->discount_amount,
                ];
            });
            return $suppliers;
        }

        if ($table === 'items') {

            $items = Item::ItemIsNotInput()
            ->whereWarehouse()
                ->whereIsActive();
            $configuration =  Configuration::getConfig();

            if ($configuration->isShowServiceOnPos() !== true) {
                $items->where('unit_type_id', '!=', 'ZZ');
            }

            $items = $items->where('series_enabled', 0)
                ->orderBy('description')
                ->take(20)
                ->get();
            $itemIds = $items->pluck('id')->toArray();
            $category_ids = $items->pluck('category_id')->unique()->toArray();
            $brand_ids = $items->pluck('brand_id')->unique()->toArray();
            $categories = Category::whereIn('id', $category_ids)->get();

            $warehousesData = DB::connection('tenant')
                ->table('item_warehouse')
                ->join('warehouses', 'item_warehouse.warehouse_id', '=', 'warehouses.id')
                ->select(
                    'item_warehouse.item_id',
                    'item_warehouse.warehouse_id',
                    'item_warehouse.stock',
                    'warehouses.description as warehouse_description'
                )
                ->whereIn('item_warehouse.item_id', $itemIds)
                ->get()
                ->groupBy('item_id');
            $sizesData = DB::connection('tenant')
                ->table('item_sizes')
                ->select('item_sizes.item_id', 'item_sizes.stock', 'item_sizes.warehouse_id', 'item_sizes.size')
                ->whereIn('item_sizes.item_id', $itemIds)
                ->get()
                ->groupBy('item_id');

            $setsData = DB::connection('tenant')
                ->table('item_sets')
                ->join('items', 'item_sets.individual_item_id', '=', 'items.id')
                ->select('item_sets.item_id', 'items.description as individual_item_description', 'item_sets.quantity')
                ->whereIn('item_sets.item_id', $itemIds)
                ->get()
                ->groupBy('item_id');

            $paymentConditionData = DB::connection('tenant')
                ->table('item_price_payment_condition')
                ->select('id', 'item_id', 'payment_condition_id', 'price')
                ->whereIn('item_id', $itemIds)
                ->get()
                ->groupBy('item_id');

        
            $brands = Brand::whereIn('id', $brand_ids)->get();
            $items = $items->transform(function (Item $row) use ($configuration, $categories, $brands, $warehousesData, $sizesData, $setsData, $paymentConditionData) {
                $full_description = ($row->internal_id) ? $row->internal_id . ' - ' . $row->description : $row->description;
                $brand = $brands->where('id', $row->brand_id)->first();
                //($row->brand->name) ? $row->description . ' - ' . $row->brand->name : $row->description,
                $description = ($brand) ? $full_description . ' - ' . $brand->name : $full_description;
                return [
                    'id' => $row->id,
                    'payment_conditions' => $paymentConditionData->get($row->id, collect()),
                    'item_id' => $row->id,
                    'sizes' => $sizesData->get($row->id, collect()),
                    'factory_code' => $row->factory_code,
                    'full_description' => $full_description,
                    'description' => $description,
                    'currency_type_id' => $row->currency_type_id,
                    'internal_id' => $row->internal_id,
                    'currency_type_symbol' => $row->currency_type->symbol,
                    'sale_unit_price' => number_format($row->sale_unit_price, $configuration->decimal_quantity, ".", ""),
                    'purchase_unit_price' => $row->purchase_unit_price,
                    'unit_type_id' => $row->unit_type_id,
                    'unit_type_symbol' => optional($row->unit_type)->symbol ?? $row->unit_type_id,
                    'aux_unit_type_id' => $row->unit_type_id,
                    'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                    'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                    'calculate_quantity' => (bool)$row->calculate_quantity,
                    'has_igv' => (bool)$row->has_igv,
                    'is_set' => (bool)$row->is_set,
                    'edit_unit_price' => false,
                    'can_edit_price' => (bool)$row->can_edit_price,
                    'aux_quantity' => 1,
                    'edit_sale_unit_price' => number_format($row->sale_unit_price, $configuration->decimal_quantity, ".", ""),
                    'aux_sale_unit_price' => number_format($row->sale_unit_price, $configuration->decimal_quantity, ".", ""),
                    'image_url' => ($row->image !== 'imagen-no-disponible.jpg') ? asset('storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'items' . DIRECTORY_SEPARATOR . $row->image) : asset("/logo/{$row->image}"),
                    'warehouses' => collect($warehousesData->get($row->id, collect()))->map(function ($warehouse) {
                        return [
                            'warehouse_id' => $warehouse->warehouse_id,
                            'warehouse_description' => $warehouse->warehouse_description,
                            'stock' => $warehouse->stock,
                        ];
                    }),
                    'category_id' => $row->category_id,
                    'sets' => $setsData->get($row->id, collect())->map(function ($set) {
                        return [
                            $set->individual_item_description,

                        ];
                    }),
                    'unit_type' => $row->item_unit_types,
                    'category' => ($row->category_id) ? $categories->where('id', $row->category_id)->first()->name : "",
                    'brand' => ($row->brand_id) ? $brands->where('id', $row->brand_id)->first()->name : "",
                    'has_plastic_bag_taxes' => (bool)$row->has_plastic_bag_taxes,
                    'amount_plastic_bag_taxes' => $row->amount_plastic_bag_taxes,

                    'has_isc' => (bool)$row->has_isc,
                    'system_isc_type_id' => $row->system_isc_type_id,
                    'percentage_isc' => $row->percentage_isc,

                    'exchange_points' => $row->exchange_points,
                    'quantity_of_points' => $row->quantity_of_points,
                    'exchanged_for_points' => false, //para determinar si desea canjear el producto
                    'used_points_for_exchange' => null, //total de puntos
                    'original_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                    'restrict_sale_cpe' => $row->restrict_sale_cpe,
                ];
            });
            return $items;
        }


        if ($table === 'card_brands') {

            $card_brands = CardBrand::all();
            return $card_brands;
        }

        return [];
    }

    public function payment()
    {
        return view('tenant.pos.payment');
    }

    public function status_configuration()
    {

        $configuration = Configuration::first();

        return $configuration;
    }

    private static $cachedConfig = null;
    private static $cachedWarehouse = null;
    private static $cachedInventoryConfig = null;

    public function validate_stock($item_id, $quantity)
    {
        $configuration = $this->getCachedConfiguration();
        $inventory_configuration = $this->getCachedInventoryConfiguration();
        $warehouse = $this->getWarehouseForValidation($configuration);

        $item = Item::with(['sets.individual_item:id,description,unit_type_id'])
            ->select('id', 'is_set', 'unit_type_id', 'description')
            ->findOrFail($item_id);

        if ($this->isServiceItem($item->unit_type_id)) {
            return ['success' => true, 'message' => ''];
        }

        if ($item->is_set) {
            return $this->validateSetItemStock($item, $quantity, $warehouse, $inventory_configuration);
        } else {
            return $this->validateSingleItemStock($item_id, $quantity, $warehouse, $inventory_configuration);
        }
    }

    private function getCachedConfiguration()
    {
        if (self::$cachedConfig === null) {
            self::$cachedConfig = Configuration::select('list_items_by_warehouse')->first();
        }
        return self::$cachedConfig;
    }

    private function getCachedInventoryConfiguration()
    {
        if (self::$cachedInventoryConfig === null) {
            self::$cachedInventoryConfig = InventoryConfiguration::select('stock_control')->firstOrFail();
        }
        return self::$cachedInventoryConfig;
    }

    private function getWarehouseForValidation($configuration)
    {
        if (self::$cachedWarehouse === null) {
            if ($configuration->list_items_by_warehouse) {
                self::$cachedWarehouse = Warehouse::select('id')
                    ->where('establishment_id', auth()->user()->establishment_id)
                    ->first();
            } else {
                $establishment = $configuration->getMainWarehouse();
                self::$cachedWarehouse = Warehouse::select('id')
                    ->where('establishment_id', $establishment->id)
                    ->first();
            }
        }
        return self::$cachedWarehouse;
    }

    private function isServiceItem($unitTypeId): bool
    {
        return $unitTypeId === 'ZZ';
    }

    private function validateSetItemStock($item, $quantity, $warehouse, $inventory_configuration)
    {
        $quantity = 1 * $quantity;
        $sets = $item->sets;

        $itemIds = $sets->pluck('individual_item.id')->toArray();
        $itemWarehouses = ItemWarehouse::where('warehouse_id', $warehouse->id)
            ->whereIn('item_id', $itemIds)
            ->select('item_id', 'stock')
            ->get()
            ->keyBy('item_id');

        foreach ($sets as $set) {
            $individual_item = $set->individual_item;
            $individual_quantity = $set->quantity * 1;
            $total_item_quantity = $individual_quantity * $quantity;

            $item_warehouse = $itemWarehouses->get($individual_item->id);

            if (!$item_warehouse) {
                return [
                    'success' => false,
                    'message' => "El producto seleccionado no está disponible en su almacén!"
                ];
            }

            if (!$this->isServiceItem($individual_item->unit_type_id)) {
                $stock = $item_warehouse->stock - $total_item_quantity;

                if ($inventory_configuration->stock_control && $stock < 0) {
                    return [
                        'success' => false,
                        'message' => "El producto {$individual_item->description} registrado en el conjunto {$item->description} no tiene suficiente stock!"
                    ];
                }
            }
        }

        return ['success' => true, 'message' => ''];
    }

    private function validateSingleItemStock($item_id, $quantity, $warehouse, $inventory_configuration)
    {
        $item_warehouse = ItemWarehouse::where([['item_id', $item_id], ['warehouse_id', $warehouse->id]])
            ->select('stock')
            ->first();

        if (!$item_warehouse) {
            return [
                'success' => false,
                'message' => "El producto seleccionado no está disponible en su almacén!"
            ];
        }

        try{
            $stock = $item_warehouse->stock - $quantity;
        } catch (\Exception $e) {
            Log::error("Error validateSingleItemStock: " . $item_warehouse->stock . " - " . $quantity);
            return [
                'success' => false,
                'message' => "El producto no tiene suficiente stock!"
            ];
        }

        if ($inventory_configuration->stock_control && $stock < 0) {
            return [
                'success' => false,
                'message' => "El producto no tiene suficiente stock!"
            ];
        }

        return ['success' => true, 'message' => ''];
    }

    /**
     * Lista inicial de items en POS
     *
     * @param Request $request
     *
     * @return PosCollection
     */
    public function item(Request $request)
    {
        $favorites = $request->favorites;
        $items = Item::ItemIsNotInput()
            ->whereWarehouse()
            ->whereIsActive()
            ->where('series_enabled', 0)
            ->orderBy('description');
        $config = Configuration::getConfig();
        if ($config->isShowServiceOnPos() !== true) {
            $items->where('unit_type_id', '!=', 'ZZ');
        }
        if ($favorites) {
            $items->where('frequent', 1);
        }
        if ($request->garage == 1) {
            $items->where('calculate_quantity', 1);
        }

        self::FilterItem($items, $request);
        if ($favorites) {
            return new PosCollection($items->paginate(10));
        } else {
            $per_page = $request->per_page;
            return new PosCollection($items->paginate($per_page ?? 30));
        }
    }
    public function get_item_service_pos(Request $request)
    {
        Item::createItemService();
        $items = Item::whereWarehouse()
            ->where('internal_id', 'ZZ001')
            ->limit(1)
            ->get();
        //si no existe crearlo por defecto

        return  new PosCollection($items);
    }
    /**
     * Unificacion de los filtros de busqueda de items en POS
     * Se evalua categoria como $request->cat
     * se evalua description, internal_id del item como $request->input_item
     * se evalua name de brand y category como $request->input_item
     *
     * @param Item    $item
     * @param Request $request
     */
    public static function FilterItem(&$item, Request $request)
    {
        $whereItem = [];
        $whereExtra = [];
        $configuration = Configuration::getConfig();
        if ($request->cat && !empty($request->cat)) {
            $whereItem[] = ['category_id', $request->cat];
        }

        if ($request->favorites) {
            $whereItem[] = ['frequent', 1];
        }

        if ($request->input_item && !empty($request->input_item)) {
            $whereItem[] = ['description', 'like', '%' . str_replace(' ', '%', $request->input_item) . '%'];
            $whereItem[] = ['barcode', '=', str_replace(' ', '%', $request->input_item)];
            $whereItem[] = ['internal_id', 'like', '%' . str_replace(' ', '%', $request->input_item) . '%'];
            $whereExtra[] = ['name', 'like', '%' . str_replace(' ', '%', $request->input_item) . '%'];
            if ($configuration->search_by_model_and_description) {
                $whereItem[] = ['model', 'like', '%' . str_replace(' ', '%', $request->input_item) . '%'];
                $whereItem[] = ['name', 'like', '%' . str_replace(' ', '%', $request->input_item) . '%'];
            }
        }

        foreach ($whereItem as $index => $wItem) {
            if ($index < 1) {
                $item->Where([$wItem]);
            } else {
                $item->orWhere([$wItem]);
            }
        }

        if (!empty($whereExtra)) {
            $item
                ->orWhereHas('brand', function ($query) use ($whereExtra) {
                    $query->where($whereExtra);
                })
                ->orWhereHas('category', function ($query) use ($whereExtra) {
                    $query->where($whereExtra);
                });
        }

        $item->whereIsActive();
    }


    public function favorite(Request $request)
    {
        $item = Item::find($request->item_id);
        $item->frequent = !$item->frequent;
        $item->save();
        return response()->json(['success' => true]);
    }

    /**
     * Se busca items al escribir en input_item desde POS
     *
     * @param Request $request
     *
     * @return PosCollection
     */
    public function search_items_cat(Request $request)
    {
        $configuration = Configuration::first();
        $per_page = $request->per_page;
        if ($configuration->active_warehouse_prices == true) {
            $item = Item::ItemIsNotInput();
        } else {
            $item = Item::ItemIsNotInput()->whereWarehouse();
            // ->where('series_enabled', 1);yy
        }


        self::FilterItem($item, $request);
        return new PosCollection($item->paginate($per_page ?? 30));
    }

    public function getItemBase()
    {

        $item = Item::checkIfExistBaseItem();
        return new PosCollection(Item::where('id', $item->id)->paginate(1));
    }

    /**
     * vista de venta rapida para POS
     *
     * @param
     *
     * @return view
     */
    public function fast()
    {
        $cash = Cash::where([['user_id', User::getUserCashId()], ['state', true]])->first();

        if (!$cash) return redirect()->route('tenant.cash.index');

        $configuration = Configuration::first();

        $company = Company::select('soap_type_id')->first();
        $soap_company  = $company->soap_type_id;
        $business_turns = BusinessTurn::select('active')->where('id', 4)->first();

        return view('tenant.pos.fast', compact('configuration', 'soap_company', 'business_turns'));
    }

    public function garage()
    {
        $cash = Cash::where([['user_id',  User::getUserCashId()], ['state', true]])->first();

        if (!$cash) return redirect()->route('tenant.cash.index');

        $configuration = Configuration::first();

        $company = Company::select('soap_type_id')->first();
        $soap_company  = $company->soap_type_id;
        $business_turns = BusinessTurn::select('active')->where('id', 4)->first();

        return view('tenant.pos.garage', compact('configuration', 'soap_company', 'business_turns'));
    }

    /**
     * Search customers with remote filtering for POS
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchCustomers(Request $request)
    {
        $input = trim($request->input('search', ''));
        $limit = $request->input('limit', 20);

        // Mínimo 2 caracteres para búsqueda
        if (strlen($input) < 2) {
            return response()->json([]);
        }

        $query = Person::with('telephones:id,person_id,telephone')
            ->whereType('customers')
            ->whereIsEnabled()
            ->orderBy('name');

        // Filtrar por descripción (nombre/documento)
        $query->where(function($q) use ($input) {
            $q->where('name', 'like', "%{$input}%")
              ->orWhere('number', 'like', "%{$input}%");
        });

        // Filtrar por código de barras si se proporciona
        if ($request->has('barcode')) {
            $query->orWhere('barcode', $input);
        }

        // Filtrar por teléfono si está habilitado
        if ($request->boolean('search_by_phone')) {
            $query->orWhereHas('telephones', function($q) use ($input) {
                $q->where('telephone', 'like', "%{$input}%");
            });
        }

        $customers = $query->limit($limit)->get();

        return response()->json($this->formatCustomersResponse($customers));
    }

    /**
     * Format customers response with telephones data
     *
     * @param \Illuminate\Database\Eloquent\Collection $customers
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function formatCustomersResponse($customers)
    {
        $customers_id = $customers->pluck('id')->toArray();
        $telephones = TelephonePerson::whereIn('person_id', $customers_id)->get();

        return $customers->transform(function ($row) use ($telephones) {
            $telephone = $row->telephone ?: '';
            $customer_telephones = $telephones->where('person_id', $row->id)->pluck('telephone')->toArray();

            if ($telephone) {
                $customer_telephones[] = $telephone;
            }

            return [
                'telephones' => $customer_telephones,
                'id' => $row->id,
                'barcode' => $row->barcode,
                'description' => $row->number . ' - ' . $row->name,
                'name' => $row->name,
                'number' => $row->number,
                'identity_document_type_id' => $row->identity_document_type_id,
                'identity_document_type_code' => $row->identity_document_type->code,
                'has_discount' => $row->has_discount,
                'discount_type' => $row->discount_type,
                'discount_amount' => $row->discount_amount,
            ];
        });
    }

    /**
     * Get batch images as base64 data (no individual HTTP requests needed)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getImageBatch(Request $request)
    {
        $filenames = $request->input('filenames', []);

        if (empty($filenames) || !is_array($filenames)) {
            return response()->json(['images' => []]);
        }

        // Filtrar archivos que no sean la imagen por defecto
        $validFilenames = array_filter($filenames, function($filename) {
            return $filename !== 'imagen-no-disponible.jpg' && !empty($filename);
        });

        $images = [];

        foreach ($validFilenames as $filename) {
            $path = storage_path("app/public/uploads/items/{$filename}");

            // Si la imagen no existe, usar imagen por defecto
            if (!file_exists($path)) {
                $path = public_path("logo/imagen-no-disponible.jpg");
                if (!file_exists($path)) {
                    $path = public_path("logo/imagen-no-disponible.png");
                    if (!file_exists($path)) {
                        continue; // Saltar si no hay fallback
                    }
                }
            }

            try {
                // Leer y convertir imagen a base64
                $imageData = file_get_contents($path);
                $mimeType = mime_content_type($path);

                // Crear data URL con base64
                $base64 = base64_encode($imageData);
                $dataUrl = "data:{$mimeType};base64,{$base64}";

                $images[] = [
                    'filename' => $filename,
                    'data_url' => $dataUrl,
                    'mime_type' => $mimeType,
                    'size' => strlen($imageData)
                ];

            } catch (\Exception $e) {
                \Log::warning("Error loading image batch: {$filename} - " . $e->getMessage());
                continue;
            }
        }

        return response()->json([
            'images' => $images,
            'count' => count($images),
            'total_size' => array_sum(array_column($images, 'size'))
        ], 200, [
            'Cache-Control' => 'public, max-age=3600', // Cache por 1 hora
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Critical data for POS - Essential data needed to show the basic interface
     * Contains only essential data needed to display the POS interface
     */
    public function tablesCritical()
    {
        $user = auth()->user();
        $establishment = $user->establishment;

        // Critical data for POS display (optimized selects)
        $company = Company::select('id', 'name', 'number', 'logo')->first();

        // Basic configuration (using same method as original)
        $configuration = Configuration::getConfig();

        // Currency types (essential for transactions)
        $currency_types = CurrencyType::whereActive()->get();

        // Categories (essential for navigation)
        $categories = Category::all();

        // User and establishment data
        $establishment_data = [
            'id' => $establishment->id,
            'description' => $establishment->description,
        ];

        $user_data = [
            'id' => $user->id,
            'name' => $user->name,
            'type' => $user->type,
            'establishment_id' => $user->establishment_id,
        ];

        // Basic series if pos_direct is enabled
        $series = [];
        $user_default_document = [];
        if ($configuration->pos_direct) {
            $series = Series::whereEstablishmentId($establishment->id)
                ->select('id', 'number', 'document_type_id', 'establishment_id')
                ->get();

            $user_default_document = UserDefaultDocumentType::where('user_id', $user->id)
                ->select('document_type_id', 'series_id', 'user_id')
                ->get();
        }

        return compact(
            'company',
            'configuration',
            'currency_types',
            'categories',
            'establishment',
            'user',
            'series',
            'user_default_document'
        );
    }

    /**
     * Secondary data for POS - Additional data loaded in background
     * Contains all additional data for full POS functionality
     */
    public function tablesSecondary()
    {
        $configuration = Configuration::getConfig();
        $user = auth()->user();
        $establishment = $user->establishment;

        // Load all secondary data (following original table('customers') method)
        $customers = Person::with('telephones:id,person_id,telephone')
            ->whereType('customers')
            ->whereIsEnabled()
            ->orderBy('name')
            ->take(20)
            ->get();

        $customers_id = $customers->pluck('id')->toArray();
        $telephones = TelephonePerson::whereIn('person_id', $customers_id)->get();

        $customers = $customers->transform(function ($row) use ($telephones) {
            $telephone = ($row->telephone) ? $row->telephone : '';
            $telephonesList = $telephones->where('person_id', $row->id)->pluck('telephone')->toArray();
            if ($telephone) {
                array_push($telephonesList, $telephone);
            }
            return [
                'telephones' => $telephonesList,
                'id' => $row->id,
                'barcode' => $row->barcode,
                'description' => $row->number . ' - ' . $row->name,
                'name' => $row->name,
                'number' => $row->number,
                'identity_document_type_id' => $row->identity_document_type_id,
                'identity_document_type_code' => $row->identity_document_type->code,
                'has_discount' => $row->has_discount,
                'discount_type' => $row->discount_type,
                'discount_amount' => $row->discount_amount,
            ];
        });

        // Default customer (from establishment customer_id)
        $customer_default = null;
        $customer_id = $establishment->customer_id;
        if ($customer_id) {
            $customer_db = DB::connection('tenant')->table('persons')
                ->select('id', 'name', 'number')
                ->where('id', $customer_id)
                ->first();
            if ($customer_db) {
                $customer_default = [
                    'id' => $customer_db->id,
                    'description' => $customer_db->number . ' - ' . $customer_db->name,
                ];
            }
        }

        // Sellers (using same method as original)
        $sellers = $this->getSellers();

        $payment_method_types = [];
        $cards_brand = [];
        $global_discount_types = [];

        if ($configuration->pos_direct) {
            // Payment methods (only if pos_direct)
            $payment_method_types = ConditionBlockPaymentMethod::getCashPaymentMethods()
                ->transform(function ($row) {
                    return [
                        'id' => $row->id,
                        'description' => $row->description,
                        'show_in_pos' => $row->show_in_pos,
                    ];
                });

            // Card brands (using same method as original)
            $cards_brand = CardBrand::all();

            // Global discount types (using same method as original)
            $global_discount_types = ChargeDiscountType::whereIn('id', ['02', '03'])->whereActive()->get();
        }

        // IGV affectation types (using same method as original)
        $affectation_igv_types = AffectationIgvType::whereActive()->get();

        // Items for food dealer (using same method as original)
        $items_food_dealer = [];
        if (BusinessTurn::isFoodDealer()) {
            $items_food_dealer = $this->get_items_food_dealer();
        }

        return compact(
            'customers',
            'customer_default',
            'sellers',
            'payment_method_types',
            'cards_brand',
            'global_discount_types',
            'affectation_igv_types',
            'items_food_dealer'
        );
    }
}

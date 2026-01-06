<?php

namespace Modules\Ecommerce\Http\Controllers;

use App\Http\Controllers\Tenant\EmailController;
use App\Models\Tenant\Configuration;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Item;
use App\Http\Resources\Tenant\ItemCollection;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant\User;
use App\Models\Tenant\Person;
use Illuminate\Support\Str;
use App\Models\Tenant\Order;
use App\Models\Tenant\ItemsRating;
use App\Models\Tenant\ConfigurationEcommerce;
use Modules\Ecommerce\Http\Resources\ItemBarCollection;
use stdClass;
use Illuminate\Support\Facades\Mail;
use App\Mail\Tenant\CulqiEmail;
use App\Http\Controllers\Tenant\Api\ServiceController;
use Illuminate\Support\Facades\Validator;
use Modules\Inventory\Models\InventoryConfiguration;
use App\Http\Resources\Tenant\OrderCollection;
use App\Models\Tenant\Company;
use App\Models\Tenant\Promotion;
use Exception;
use PhpParser\Node\Expr\Cast\Object_;

class EcommerceController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function __construct()
    {
        return view()->share('records', Item::where('apply_store', 1)->orderBy('id', 'DESC')->take(2)->get());
    }

    public function index()
    {
        $columns = 3;
        $configuration = ConfigurationEcommerce::first();
        if ($configuration && $configuration->columns_virtual_store) {
            $columns = $configuration->columns_virtual_store;
        }
        $dataPaginate = Item::where([['apply_store', 1], ['internal_id', '!=', null]])
            ->with('clientTypePrices')
            ->paginate(15);
        $favicon = $configuration->favicon;
        $configuration = InventoryConfiguration::first();
        $company = Company::first();
        $trade_name = ($company) ? $company->trade_name : 'Ecommerce';
        $person_type_id = auth()->user() ? auth()->user()->person_type_id : null;
        return view('ecommerce::index', [
            'person_type_id' => $person_type_id,
            'favicon' => $favicon,
            'dataPaginate' => $dataPaginate, 'configuration' => $configuration->stock_control, 'columns' => $columns,
            'trade_name' => $trade_name,
        ]);
    }

    public function category(Request $request)
    {
        $columns = 3;
        $configuration = ConfigurationEcommerce::first();
        if ($configuration && $configuration->columns_virtual_store) {
            $columns = $configuration->columns_virtual_store;
        }
        $favicon = $configuration->favicon;
        $company = Company::first();
        $trade_name = ($company) ? $company->trade_name : 'Ecommerce';
        $dataPaginate = Item::select('i.*')
            ->where([['i.apply_store', 1], ['i.internal_id', '!=', null], ['it.tag_id', $request->category]])
            ->from('items as i')
            ->join('item_tags as it', 'it.item_id', 'i.id')->paginate(15);
        $configuration = InventoryConfiguration::first();
        $person_type_id = auth()->user() ? auth()->user()->person_type_id : null;
        return view('ecommerce::index', [
            'person_type_id' => $person_type_id,
            'favicon' => $favicon,
            'dataPaginate' => $dataPaginate, 'configuration' => $configuration->stock_control, 'columns' => $columns,
            'trade_name' => $trade_name
        ]);
    }

    public function userIndex()
    {

        return view('ecommerce::users.index');
    }
    public function getDescriptionWithPromotion($item, $promotion_id)
    {
        $promotion = Promotion::findOrFail($promotion_id);

        return "{$item->description} - {$promotion->name}";
    }

    public function item($id, $promotion_id = null)
    {
        $row = Item::find($id);
        $exchange_rate_sale = $this->getExchangeRateSale();
        $sale_unit_price = ($row->has_igv) ? $row->sale_unit_price : $row->sale_unit_price * 1.18;

        $description = $promotion_id ? $this->getDescriptionWithPromotion($row, $promotion_id) : $row->description;

        $record = (object)[
            'id' => $row->id,
            'internal_id' => $row->internal_id,
            'unit_type_id' => $row->unit_type_id,
            'description' => $description,
            'technical_specifications' => $row->technical_specifications,
            'name' => $row->name,
            'second_name' => $row->second_name,
            'sale_unit_price' => ($row->currency_type_id === 'PEN') ? $sale_unit_price : ($sale_unit_price * $exchange_rate_sale),
            'currency_type_id' => $row->currency_type_id,
            'has_igv' => (bool) $row->has_igv,
            'sale_unit' => $row->sale_unit_price,
            'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
            'currency_type_symbol' => $row->currency_type->symbol,
            'image' =>  $row->image,
            'item_unit_types' => $row->item_unit_types,
            'image_medium' => $row->image_medium,
            'image_small' => $row->image_small,
            'tags' => $row->tags->pluck('tag_id')->toArray(),
            'images' => $row->images,
            'attributes' => $row->attributes ? $row->attributes : [],
            'promotion_id' => $promotion_id,
        ];

        return view('ecommerce::items.record', compact('record'));
    }

    public function items()
    {
        $records = Item::where('apply_store', 1)->get();
        return view('ecommerce::items.index', compact('records'));
    }

    public function itemsBar()
    {
        $records = Item::where('apply_store', 1)->get();
        // return new ItemCollection($records);
        return new ItemBarCollection($records);
    }

    public function partialItem($id)
    {
        $record = Item::with('clientTypePrices')->find($id);
        $person_type_id = auth()->user() ? auth()->user()->person_type_id : null;
        $configuration = ConfigurationEcommerce::first();
        return view('ecommerce::items.partial', compact('record', 'configuration', 'person_type_id'));
    }

    public function detailCart()
    {
        $configuration = ConfigurationEcommerce::first();

        $history_records = [];
        if (auth()->user()) {
            $email_user = auth()->user()->email;
            $history_records = Order::where('customer', 'LIKE', '%' . $email_user . '%')
                ->get()
                ->transform(function ($row) {
                    /** @var  Order $row */
                    return $row->getCollectionData();
                })->toArray();
        }
        return view('ecommerce::cart.detail', compact(['configuration', 'history_records']));
    }

    public function pay()
    {
        return view('ecommerce::cart.pay');
    }

    public function showLogin()
    {
        return view('ecommerce::user.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            return [
                'success' => true,
                'message' => 'Login Success'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Usuario o Password incorrectos'
            ];
        }
    }

    public function logout()
    {
        Auth::logout();
        return [
            'success' => true,
            'message' => 'Logout Success'
        ];
    }
    public function getUsers()
    {
        $users = User::where('type', 'client')->get();
    }
    public function recordUser($id)
    {
        $user = User::findOrFail($id);
        $user_response = new \stdClass();
        $user_response->name = $user->name;
        $user_response->email = $user->email;
        $user_response->person_id = $user->person_id;
        $user_response->edit_price_pos = $user->edit_price_pos != null ? $user->edit_price_pos : false;
        return $user_response;
    }
    public function updateUser(Request $request)
    {
        $id = $request->id;
        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->person_type_id = $request->person_type_id;
        $user->email = $request->email;
        $user->person_id = $request->person_id;
        $user->edit_price_pos = $request->edit_price_pos != null ? $request->edit_price_pos : false;
        $user->save();
        return [
            'success' => true,
            'message' => 'Usuario actualizado'
        ];
    }
    public function storeUser(Request $request)
    {
        try {

            $verify = User::where('email', $request->email)->first();
            if ($verify) {
                return [
                    'success' => false,
                    'message' => 'Email no disponible'
                ];
            }

            $person_varios = Person::whereFilterVariousClients()->first();
            $person_id = null;
            if ($person_varios) {
                $person_id = $person_varios->id;
            }

            $is_from_admin = $request->is_from_admin;
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->establishment_id = 1;
            $user->person_id = $request->person_id;
            $user->person_type_id = $request->person_type_id;
            $user->edit_price_pos = $request->edit_price_pos != null ? $request->edit_price_pos : false;
            $user->type = 'client';
            $user->api_token = str_random(50);
            $user->password = bcrypt($request->pswd);
            if (!$request->person_id && $person_id) {
                $user->person_id = $person_id;
            }
            $user->save();
            $user->modules()->sync([10]);

            $credentials = ['email' => $user->email, 'password' => $request->pswd];
            if (!$is_from_admin) {
                Auth::attempt($credentials);
            }
            return [
                'success' => true,
                'message' => 'Usuario registrado'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' =>  $e->getMessage()
            ];
        }
    }

    public function transactionFinally(Request $request)
    {
        try {
            /** @var User $user */
            $user = auth()->user();
            //1. confirmar dato de compriante en order
            $order_generated = Order::find($request->orderId);
            $order_generated->document_external_id = $request->document_external_id;
            $order_generated->number_document = $request->number_document;
            $order_generated->save();

            $user->update(['identity_document_type_id' => $request->identity_document_type_id, 'number' => $request->number]);
            return [
                'success' => true,
                'message' => 'Order Actualizada',
                'order_total' => $order_generated->total
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' =>  $e->getMessage()
            ];
        }
    }

    public function paymentCash(Request $request)
    {
        $rules = [
            'telefono' => 'required|numeric',
            'codigo_tipo_documento_identidad' => 'required|numeric',
            'numero_documento' => 'required|numeric',
            'identity_document_type_id' => 'required|numeric'
        ];

        if ($request->is_delivery) {
            $rules['direccion'] = 'required';
        }

        $validator = Validator::make($request->customer, $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        } else {
            try {
                $user = auth()->user();
                $order = Order::create([
                    'external_id' => Str::uuid()->toString(),
                    'customer' =>  $request->customer,
                    'shipping_address' => $request->address,
                    'items' =>  $request->items,
                    'total' => $request->precio_culqi,
                    'reference_payment' => $request->reference_payment,
                    'status_order_id' => 1,
                    'purchase' => $request->purchase,
                    'is_delivery' => $request->is_delivery,
                    'street' => $request->street,
                    'number' => $request->number,
                    'reference' => $request->reference

                ]);

                $customer_email = $user->email;
                $document = new stdClass;
                $document->client = $user->name;
                $document->product = $request->producto;
                $document->total = $request->precio_culqi;
                $document->items = $request->items;

                $this->paymentCashEmail($customer_email, $document);

                //Mail::to($customer_email)->send(new CulqiEmail($document));
                return [
                    'success' => true,
                    'order' => $order
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' =>  $e->getMessage()
                ];
            }
        }
    }

    public function paymentCashEmail($customer_email, $document)
    {
        try {
            $email = $customer_email;
            $mailable = new CulqiEmail($document);
            $id = (int) $document->id;
            $model = __FILE__ . ";;" . __LINE__;
            $sendIt = EmailController::SendMail($email, $mailable, $id, $model);
            /*
            Configuration::setConfigSmtpMail();
            $array_email = explode(',', $customer_email);
            if (count($array_email) > 1) {
                foreach ($array_email as $email_to) {
                    $email_to = trim($email_to);
                if(!empty($email_to)) {
                        Mail::to($email_to)->send(new CulqiEmail($document));
                    }
                }
            } else {
                Mail::to($customer_email)->send(new CulqiEmail($document));
            }*/
        } catch (\Exception $e) {
            return true;
        }
    }

    public function ratingItem(Request $request)
    {
        if (auth()->user()) {
            $user_id = auth()->id();
            $row = ItemsRating::firstOrNew(['user_id' => $user_id, 'item_id' => $request->item_id]);
            $row->value = $request->value;
            $row->save();
            return [
                'success' => false,
                'message' => 'Rating Guardado'
            ];
        }
        return [
            'success' => false,
            'message' => 'No se guardo Rating'
        ];
    }

    public function getRating($id)
    {
        if (auth()->user()) {
            $user_id = auth()->id();
            $row = ItemsRating::where('user_id', $user_id)->where('item_id', $id)->first();
            return [
                'success' => true,
                'value' => ($row) ? $row->value : 0,
                'message' => 'Valor Obtenido'
            ];
        }
        return [
            'success' => false,
            'value' => 0,
            'message' => 'No se obtuvo valor'
        ];
    }

    private function getExchangeRateSale()
    {

        $exchange_rate = app(ServiceController::class)->exchangeRateTest(date('Y-m-d'));

        return (array_key_exists('sale', $exchange_rate)) ? $exchange_rate['sale'] : 1;
    }

    public function saveDataUser(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();
        if ($request->address) {
            $user->address = $request->address;
        }
        if ($user->telephone = $request->telephone) {
            $user->telephone = $request->telephone;
        }

        $user->save();

        return ['success' => true];
    }

    public function searchItems(Request $request)
    {
        $term = $request->input('term');
        $items = Item::where('description', 'like', "%{$term}%")
            ->where('apply_store', 1)
            ->take(10)
            ->get()
            ->transform(function ($row) {
                $exchange_rate_sale = $this->getExchangeRateSale();
                $sale_unit_price = ($row->has_igv) ? $row->sale_unit_price : $row->sale_unit_price * 1.18;

                return [
                    'id' => $row->id,
                    'internal_id' => $row->internal_id,
                    'unit_type_id' => $row->unit_type_id,
                    'description' => $row->description,
                    'name' => $row->name,
                    'sale_unit_price' => ($row->currency_type_id === 'PEN') ? $sale_unit_price : ($sale_unit_price * $exchange_rate_sale),
                    'currency_type_id' => $row->currency_type_id,
                    'has_igv' => (bool) $row->has_igv,
                    'sale_unit' => $row->sale_unit_price,
                    'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                    'currency_type_symbol' => $row->currency_type->symbol,
                    'image' => $row->image,
                    'image_medium' => $row->image_medium,
                    'image_small' => $row->image_small,
                ];
            });

        return response()->json($items);
    }
}

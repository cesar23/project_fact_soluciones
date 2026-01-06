<?php


namespace Modules\Ecommerce\Http\Controllers;

use App\CoreFacturalo\Requests\Api\Transform\Common\PersonTransform;
use App\CoreFacturalo\Requests\Api\Transform\Functions;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Api\QuotationController;
use App\Models\Tenant\Catalogs\IdentityDocumentType;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Item;
use App\Models\Tenant\ItemUnitType;
use App\Models\Tenant\Person;
use App\Models\Tenant\Quotation;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\User;
use App\Models\Tenant\Warehouse;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Dispatch\Models\DispatchAddress;
use Modules\Ecommerce\Models\WoocommerceConfiguration;
use Modules\Ecommerce\Models\WoocommerceItem;
use Modules\Ecommerce\Services\WoocommerceService;
use Modules\Item\Models\WebPlatform;
use Modules\Store\Http\Controllers\StoreController;

class WoocommerceController extends Controller
{

    public function sync(Request $request)
    {
        $service = new WoocommerceService();
        $info = $service->syncAllProducts();
        return ['success' => true, 'message' => 'Sincronizado correctamente', 'info' => $info];
    }

    public function syncOne(Request $request)
    {
        $item_id = $request->id;
        $item = Item::find($item_id);
        $service = new WoocommerceService();
        $exist = $service->checkIfExistSku($item->internal_id);
        if ($exist) {
            return $service->updateProduct($item);
        }
        return $service->createProduct($item);
    }

    private function syncronice_woocommerce_one($id)
    {
        $item = Item::find($id);
    }

    public function index()
    {

        $woocommerceConfiguration = WoocommerceConfiguration::first();


        return view('ecommerce::woocommerce.index', compact('woocommerceConfiguration'));
    }
    public function has_woocommerce()
    {
        $woocommerceConfiguration = WoocommerceConfiguration::first();
        $key = $woocommerceConfiguration["woocommerce_api_key"];
        $secret = $woocommerceConfiguration["woocommerce_api_secret"];
        $url = $woocommerceConfiguration["woocommerce_api_url"];
        if (!empty(trim($key)) && !empty(trim($secret)) && !empty(trim($url))) {
            return [
                'success' => true
            ];
        }
        return [
            'success' => false
        ];
    }
    public function configurations()
    {
        $woocommerceConfiguration = WoocommerceConfiguration::first();
        return response()->json($woocommerceConfiguration);
    }
    public function syncronice_woocommerce()
    {
        $insert = 0;

        $woocommerceConfiguration = WoocommerceConfiguration::first();
        $page = 1;
        $key = $woocommerceConfiguration["woocommerce_api_key"];
        $secret = $woocommerceConfiguration["woocommerce_api_secret"];
        $url = $woocommerceConfiguration["woocommerce_api_url"];
        $uri_base = $url . '/products' . '?consumer_key=' . $key . '&consumer_secret=' . $secret;
        $last_sync = $woocommerceConfiguration['woocommerce_api_last_sync'];
        $more_page = true;
        if ($last_sync) {
            $uri_base = $uri_base . "&after=" . $last_sync;
        }

        $amount_plastic_bag_taxes = Configuration::firstOrFail()->amount_plastic_bag_taxes;
        $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
        $warehouse = Warehouse::where('establishment_id', $establishment->id)->first();
        $last_modified = "";

        try {

            $client = new \GuzzleHttp\Client();
            while ($more_page) {
                $uri_base_ = $uri_base . '&order=asc&page=' . $page;
                $request = $client->get($uri_base_, ['verify' => false]);
                $data = json_decode($request->getBody()->getContents());

                if (empty($data)) {
                    if ($page === 1) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Sin nada que sincronizar'
                        ], 200);
                        break;
                        $more_page = false;
                    } else {
                        $more_page = false;
                        break;
                    }
                }

                foreach ($data as $product) {

                    try {
                        $exist = WoocommerceItem::where('woocommerce_item_id', $product->id)->get();

                        if (!$exist->isEmpty()) continue;
                        $sku = $product->sku;

                        if (empty($sku)) {

                            $sku = "WOO-" . $product->id;
                        }

                        $woocommerce_plataform = WebPlatform::where('name', 'Woocommerce')->first();

                        $item = new Item();
                        $item->web_platform_id = $woocommerce_plataform->id;
                        $item->description = $this->clean_name($product->name);
                        $item->internal_id = $sku;
                        $item->name = $this->clean_name($product->description);
                        $item->second_name = $product->slug;
                        $item->unit_type_id = "NIU";
                        $item->currency_type_id = str_contains($product->price_html, 'S/') ? "PEN" : "USD";
                        $item->sale_unit_price = $product->price;
                        $item->purchase_unit_price = $product->price;
                        $item->stock = (($product->stock_quantity == null) ? 1000.00 : $product->stock_quantity);
                        $item->stock_min = 1.0;
                        $item->sale_affectation_igv_type_id = 10;
                        $item->purchase_affectation_igv_type_id = 10;
                        $item->amount_plastic_bag_taxes = $amount_plastic_bag_taxes;
                        $item->warehouse_id = $warehouse->id;
                        $item->item_type_id = '01';
                        $item->apply_store = 1;

                        $item->save();
                    } catch (Exception $e) {

                        $save_last_update = WoocommerceConfiguration::first();

                        $save_last_update->update([
                            "woocommerce_api_last_sync" => $last_modified,
                        ]);

                        return [
                            "success" => false,
                            "message" =>  $e->getMessage()
                        ];
                    }
                    ++$insert;
                    if ($item->id) {
                        $last_modified = $product->date_created;
                        $product_woocommerce = new WoocommerceItem();
                        $product_woocommerce->item_id = $item->id;
                        $product_woocommerce->woocommerce_item_id = $product->id;
                        $product_woocommerce->save();
                    }
                }
                ++$page;
            }

            $save_last_update = WoocommerceConfiguration::first();

            $save_last_update->update([
                "woocommerce_api_last_sync" => $last_modified,
            ]);
            return [
                "success" => true,
                "message" =>  $insert . " Productos sincronizados"
            ];
        } catch (Exception $e) {

            return [
                'success' => false,
                'error' => $e,

            ];
        }
    }
    public function items()
    {
        $service = new WoocommerceService();
        return $service->getAllItems();
    }
    function clean_name($name)
    {
        return substr(str_replace(array('<p>', '</p>', '\n', '\r\n'), '', $name), 0, 599);
    }
    public function saveConfigurations(Request $request)
    {
        $woocommerce_plataform_exists = WebPlatform::where('name', 'Woocommerce')->first();
        if (!$woocommerce_plataform_exists) {

            try {
                $woocommerce_platform = new WebPlatform();
                $woocommerce_platform->name = 'Woocommerce';
                $woocommerce_platform->save();
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'message' => 'Error al crear nueva plataforma'
                ];
            }
        }
        $request->validate([
            'woocommerce_api_url' => 'required',
            'woocommerce_api_key' => 'required',
            'woocommerce_api_secret' => 'required',
        ]);

        $configuration = WoocommerceConfiguration::first();

        $configuration->update([
            "woocommerce_api_url" => $request['woocommerce_api_url'],
            "woocommerce_api_key" => $request['woocommerce_api_key'],
            "woocommerce_api_secret" => $request['woocommerce_api_secret'],
        ]);




        return [
            "success" => true,
            "message" => 'Se guardó la información'

        ];
    }

    public function generatorQuotation(Request $request)
    {
        $igv = (new StoreController)->getIgv(new Request());
        $billing = $request->billing;
        $shipping = $request->shipping;
        $customer = $request->customer;
        $user_store_online = User::where('name', 'like', '%Tienda Online%')->first();
        if ($user_store_online) {
            Auth::login($user_store_online);
        }

        $products = $request->products;
        $items = [];

        foreach ($products as $product) {
            $internal_id = $product['sku'];
            $item = Item::where('internal_id', $internal_id)->first();
            if (!$item) {
                continue;
            }
            $presentation = null;
            $variation_id = isset($product['variation_id']) ? $product['variation_id'] : null;
            if ($variation_id) {
                $presentation_item = ItemUnitType::where('barcode', $variation_id)->first();
                if ($presentation_item) {
                    $presentation = $presentation_item->toArray();
                }
            }
            $affectation_igv_type_id = $item->sale_affectation_igv_type_id;
            $price = $product['price'];
            $value = $product['price'];
            $has_igv = $item->has_igv;
            if ($has_igv && $affectation_igv_type_id == "10") {
                $price = $value * (1 + $igv);
            }
            $quantity = $product['quantity'];
            $total_value = $value * $quantity;
            $total_igv = 0;

            if ($affectation_igv_type_id == "10") {
                $total_igv = $total_value * $igv;
            }
            if ($affectation_igv_type_id == "20") {
                $total_igv = 0;
            }
            if ($affectation_igv_type_id == "30") {
                $total_igv = 0;
            }
            $total_taxes = $total_igv;
            $total = $total_value + $total_taxes;



            $items[] = [
                'item_id' => $item->id,
                'item' => [
                    'has_perception' => false,
                    'percentage_perception' => null,
                    'can_edit_price' => false,
                    'video_url' => null,
                    'meter' => null,
                    'bonus_items' => [],
                    'disponibilidad' => null,
                    'header' => null,
                    'id' => $item->id,
                    'item_code' => null,
                    'full_description' => $item->description,
                    'description' => $item->description,
                    'model' => null,
                    'brand' => null,
                    'brand_id' => null,
                    'category_id' => $item->category_id,
                    'stock' => $item->stock,
                    'internal_id' => $item->internal_id,
                    'description' => $item->description,
                    'currency_type_id' => $item->currency_type_id,
                    'currency_type_symbol' => $item->currency_type_symbol,
                    'has_igv' => $item->has_igv,
                    'sale_unit_price' => $item->sale_unit_price,
                    'purchase_has_igv' => $item->purchase_has_igv,
                    'purchase_unit_price' => $item->purchase_unit_price,
                    'unit_type_id' => $item->unit_type_id,
                    'sale_affectation_igv_type_id' => $item->sale_affectation_igv_type_id,
                    'purchase_affectation_igv_type_id' => $item->purchase_affectation_igv_type_id,
                    'calculate_quantity' => false,
                    'has_plastic_bag_taxes' => false,
                    'amount_plastic_bag_taxes' => '0.50',
                    'item_unit_types' => $item->item_unit_types->toArray(),
                    'warehouses' => $item->warehouses->toArray(),
                    'attributes' => [],
                    'lots_group' => [],
                    'lots' => [],
                    'is_set' => 0,
                    'barcode' => $item->barcode,
                    'lots_enabled' => false,
                    'series_enabled' => false,
                    'unit_price' => $price,
                    'warehouse_id' => $item->warehouse_id,
                    'presentation' => $presentation,
                    'used_points_for_exchange' => 0,
                ],
                'quantity' => $quantity,
                'unit_value' => $value,
                'price_type_id' => '01',
                'unit_price' => $price,
                'affectation_igv_type_id' => $affectation_igv_type_id,
                'total_base_igv' => $total_value,
                'percentage_igv' => $igv,
                'total_igv' => $total_igv,
                'total_taxes' => $total_taxes,
                'total_value' => $total_value,
                'total' => $total,
                'attributes' => [],
                'discounts' => [],
                'charges' => [],
                'warehouse_id' => $item->warehouse_id
            ];
        }



        $inputs = $this->calculateTotal($items);
        if (count($items) > 0) {
            $inputs['items'] = $items;
            $inputs['billing'] = $billing;
            $inputs['shipping'] = $shipping;
            $inputs['customer'] = $customer;
            $formatted = $this->format($inputs);
            $response = (new QuotationController)->store(
                new Request($formatted)
            );
            return response()->json($response);
        }
        return response()->json([
            'success' => false,
            'message' => 'No hay items para crear la cotización'
        ], 400);
    }

    public function calculateTotal($items)
    {
        $total_discount = 0;
        $total_charge = 0;
        $total_exportation = 0;
        $total_taxed = 0;
        $total_exonerated = 0;
        $total_unaffected = 0;
        $total_free = 0;
        $total_igv = 0;
        $total_value = 0;
        $total = 0;
        $total_igv_free = 0;
        $total_discount_no_base = 0;

        foreach ($items as $row) {


            if ($row['affectation_igv_type_id'] === "10") {
                $total_taxed += floatval($row['total_value']);
            }
            if ($row['affectation_igv_type_id'] === "20") {
                $total_exonerated += floatval($row['total_value']);
            }
            if ($row['affectation_igv_type_id'] === "30") {
                $total_unaffected += floatval($row['total_value']);
            }
            if ($row['affectation_igv_type_id'] === "40") {
                $total_exportation += floatval($row['total_value']);
            }
            if (!in_array($row['affectation_igv_type_id'], ["10", "20", "30", "40"])) {
                $total_free += floatval($row['total_value']);
            }
            if (in_array($row['affectation_igv_type_id'], ["10", "20", "30", "40"])) {
                $total_igv += floatval($row['total_igv']);
                $total += floatval($row['total']);
            }
            $total_value += floatval($row['total_value']);

            if (in_array($row['affectation_igv_type_id'], ["11", "12", "13", "14", "15", "16"])) {
                $unit_value = $row['total_value'] / $row['quantity'];
                $total_value_partial = $unit_value * $row['quantity'];
                $row['total_taxes'] = $row['total_value'] - $total_value_partial;
                $row['total_igv'] = $total_value_partial * ($row['percentage_igv'] / 100);
                $row['total_base_igv'] = $total_value_partial;
                $total_value -= $row['total_value'];
                $total_igv_free += $row['total_igv'];
            }
        }

        return [
            'date_of_issue' => now()->format('Y-m-d'),
            'time_of_issue' => now()->format('H:i:s'),
            'currency_type_id' => 'PEN',
            'exchange_rate_sale' => 1,
            'total_igv_free' => round($total_igv_free, 2),
            'total_discount' => round($total_discount, 2),
            'total_exportation' => round($total_exportation, 2),
            'total_taxed' => round($total_taxed, 2),
            'total_exonerated' => round($total_exonerated, 2),
            'total_unaffected' => round($total_unaffected, 2),
            'total_free' => round($total_free, 2),
            'total_igv_free' => 0.00,
            'total_igv' => round($total_igv, 2),
            'total_value' => round($total_value, 2),
            'total_taxes' => round($total_igv, 2),
            'total' => round($total, 2)
        ];
    }

    public function format($inputs)
    {
        $customer_info = $inputs['customer'];
        $billing_info = $inputs['billing'];
        $customer = Person::where('number', '00000000')->first();
        if ($customer_info) {
            $to_insert_customer = [
                'country_id' => 'PE',
                'type' => 'customers',

            ];
            if (isset($customer_info['doc_number'])) {
                $to_insert_customer['number'] = $customer_info['doc_number'];
            }

            if (isset($customer_info['full_name'])) {
                $to_insert_customer['name'] = $customer_info['full_name'];
            }
            if (isset($customer_info['email'])) {
                $to_insert_customer['email'] = $customer_info['email'];
            }
            if (isset($customer_info['phone'])) {
                $phone = $customer_info['phone'];
                $to_insert_customer['telephone'] = str_replace('+51', '', $phone);
            }
            if (isset($customer_info['address'])) {
                $to_insert_customer['address'] = $customer_info['address'];
            }

            if (isset($customer_info['doc_type'])) {
                $doc_type = $customer_info['doc_type'];
                $found_doc_type = IdentityDocumentType::where('description', 'like', '%' . $doc_type . '%')->first();
                if ($found_doc_type) {
                    $to_insert_customer['identity_document_type_id'] = $found_doc_type->id;
                }
            }
            if (isset($to_insert_customer['number'])) {
                $exist_customer = Person::where('number', $to_insert_customer['number'])
                    ->where('type', 'customers')
                    ->first();
                if ($exist_customer) {
                    $customer = $exist_customer;
                } else {
                    $customer = Person::create($to_insert_customer);
                }
            }
        }
        if ($billing_info) {
            $dispatch_address = DispatchAddress::where('person_id', $customer->id)->first();
            if (!$dispatch_address) {
                $dispatch_address = new DispatchAddress();
            }
            $dispatch_address->person_id = $customer->id;
            if (isset($billing_info['billing_agencia']) && $billing_info['billing_agencia'] != '') {
                $dispatch_address->agency = $billing_info['billing_agencia'];
            }
            if (isset($billing_info['billing_tipo'])) {
                $billing_type = $billing_info['billing_tipo'];
                if ($billing_type == 'recojo' && isset($billing_info['billing_address_agency']) && $billing_info['billing_address_agency'] != '') {
                    $billing_address_agency = $billing_info['billing_address_agency'];
                    $dispatch_address->address = $billing_address_agency;
                } else {
                    $dispatch_address->address = $customer_info['address'];
                }
            }

            if (isset($billing_info['billing_recibir']) && $billing_info['billing_recibir'] != '') {
                $billing_receive = $billing_info['billing_recibir'];
                if ($billing_receive == 'yo') {
                    $dispatch_address->person = $customer->name;
                    $dispatch_address->person_document = $customer->number;
                    $dispatch_address->person_telephone = $customer->telephone;
                    $dispatch_address->identity_document_type_id = $customer->identity_document_type_id;
                } else if ($billing_receive == 'otra') {
                    if (isset($billing_info['billing_recibir_nombre']) && $billing_info['billing_recibir_nombre'] != '') {
                        $dispatch_address->person = $billing_info['billing_recibir_nombre'];
                    }
                    if (isset($billing_info['billing_recibir_nro_documento']) && $billing_info['billing_recibir_nro_documento'] != '') {
                        $dispatch_address->person_document = $billing_info['billing_recibir_nro_documento'];
                    }
                    if (isset($billing_info['billing_recibir_telefono']) && $billing_info['billing_recibir_telefono'] != '') {
                        $dispatch_address->person_telephone = $billing_info['billing_recibir_telefono'];
                    }
                    if (isset($billing_info['billing_recibir_tipo_documento']) && $billing_info['billing_recibir_tipo_documento'] != '') {
                        $found_doc_type = IdentityDocumentType::where('description', 'like', '%' . $billing_info['billing_recibir_tipo_documento'] . '%')->first();
                        if ($found_doc_type) {
                            $dispatch_address->identity_document_type_id = $found_doc_type->id;
                        }
                    }
                }
            }
            if (isset($billing_info['billing_capital_location_google_maps']) && $billing_info['billing_capital_location_google_maps'] != '') {
                $dispatch_address->google_location = $billing_info['billing_capital_location_google_maps'];
            }
            if(isset($billing_info['billing_despacho']) && $billing_info['billing_despacho'] == 'almacen'){
                $dispatch_address->address = 'Calle 15 Mz R Lt 1, Urb. Asesores';
                $dispatch_address->reference = 'Frente al Real Plaza Puruchuco';
                $dispatch_address->agency = 'CAMPO GRANDE PERU';
                $dispatch_address->google_location = 'https://maps.app.goo.gl/ZJZ9ttMPzstiH6CS9';
                $dispatch_address->person = $customer->name;
                $dispatch_address->person_document = $customer->number;
                $dispatch_address->person_telephone = $customer->telephone;
                $dispatch_address->identity_document_type_id = $customer->identity_document_type_id;

            }
        }
        $company = Company::first();


        $number = 1;
        $last_id = Quotation::where('prefix', "COT")->orderBy('id', 'desc')->first();
        if ($last_id) {
            if ($last_id->number) {
                $number = $last_id->number + 1;
            } else {
                $number = $last_id->id + 1;
            }
        }
        $establishment = Establishment::first();

        $inputs_transform = [
            'user_id' => auth('api')->user()->id,
            'external_id' => Str::uuid()->toString(),
            'soap_type_id' => $company->soap_type_id,
            'establishment_id' => $establishment->id,
            'establishment' =>  json_decode(Establishment::where('id', $establishment->id)->first(), true),
            'customer_id' => $customer->id,
            'customer' => PersonTransform::transform_customer($customer),

            'state_type_id' => "01",
            'prefix' => "COT",
            'number' => $number,
            'date_of_issue' => Functions::valueKeyInArray($inputs, 'date_of_issue'),
            'time_of_issue' => Functions::valueKeyInArray($inputs, 'time_of_issue'),
            'currency_type_id' => Functions::valueKeyInArray($inputs, 'currency_type_id'),
            'exchange_rate_sale' => Functions::valueKeyInArray($inputs, 'exchange_rate_sale', 1),
            'purchase_order' => null,
            'total_prepayment' => 0.00,
            'total_discount' => 0.00,
            'total_charge' => 0.00,
            'total_exportation' => 0.00,
            'total_free' => 0.00,
            'total_prepayment' => 0.00,
            'total_discount'   => 0.00,
            'total_charge'     => 0.00,
            'total_taxed' => Functions::valueKeyInArray($inputs, 'total_taxed'),
            'total_unaffected' => Functions::valueKeyInArray($inputs, 'total_unaffected'),
            'total_exonerated' => Functions::valueKeyInArray($inputs, 'total_exonerated'),
            'total_igv' => Functions::valueKeyInArray($inputs, 'total_igv'),
            'total_igv_free' => Functions::valueKeyInArray($inputs, 'total_igv_free'),
            'total_base_isc' => 0.00,
            'total_isc' => 0.00,
            'total_base_other_taxes' => 0.00,
            'total_other_taxes' => 0.00,
            'total_plastic_bag_taxes' => 0.00,
            'total_taxes' => Functions::valueKeyInArray($inputs, 'total_taxes'),
            'total_value' => Functions::valueKeyInArray($inputs, 'total_value'),
            'subtotal' => (Functions::valueKeyInArray($inputs, 'total_value')) ? $inputs['total_value'] : $inputs['total'],
            'total' => Functions::valueKeyInArray($inputs, 'total'),
            'total_pending_payment' => 0.00,
            'has_prepayment' => 0,
            'items' => $inputs['items'],
            'additional_information' => Functions::valueKeyInArray($inputs, 'informacion_adicional'),
            'additional_data' => Functions::valueKeyInArray($inputs, 'dato_adicional'),
            'payments' => [],
            'payment_condition_id' => '01',
            'sale_note_id' => null,
            'total_detraction' => 0.00,
        ];


        return $inputs_transform;
    }
}

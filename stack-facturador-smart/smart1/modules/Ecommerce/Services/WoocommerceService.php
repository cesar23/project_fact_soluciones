<?php

namespace Modules\Ecommerce\Services;

use App\Models\Tenant\Establishment;
use App\Models\Tenant\Item;
use App\Models\Tenant\ItemWarehouse;
use App\Models\Tenant\User;
use App\Models\Tenant\Warehouse;
use Exception;
use Modules\Ecommerce\Models\WoocommerceConfiguration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Ecommerce\Models\WoocommerceItem;

class WoocommerceService
{


    protected $woocommerce_api_url;
    protected $woocommerce_api_key;
    protected $woocommerce_api_secret;
    protected $woocommerce_api_version;
    protected $woocommerce_api_last_sync;
    protected $last_id;
    protected $enabled;
    protected $user;

    const PRODUCT_URL = '/wp-json/wc/v3/products';
    const PRODUCT_BATCH_URL = '/wp-json/wc/v3/products/batch';

    public function __construct()
    {
        $woocommerceConfiguration = WoocommerceConfiguration::first();
        $this->user = auth("api")->user() ?? auth()->user();
        if (!$this->user) {
            $this->user = User::first();
        }
        $this->woocommerce_api_url = $woocommerceConfiguration->woocommerce_api_url;
        $this->woocommerce_api_key = $woocommerceConfiguration->woocommerce_api_key;
        $this->woocommerce_api_secret = $woocommerceConfiguration->woocommerce_api_secret;
        $this->woocommerce_api_version = $woocommerceConfiguration->woocommerce_api_version;
        $this->woocommerce_api_last_sync = $woocommerceConfiguration->woocommerce_api_last_sync;
        $this->last_id = $woocommerceConfiguration->last_id;
        $this->enabled = $this->isEnabled();
    }

    private function formatResponseBatch($response, $message = '')
    {
        $body = json_decode($response->body());
        $response_status = $response->status();
        if ($response_status >= 200 && $response_status < 300) {

            return response()->json(['status' => $response_status, 'data' => $body, 'message' => $message], $response_status);
        }
        Log::info(json_encode($body));
        return response()->json([
            'message' => $message . ' ' . implode(', ', array_values((array)$body->params)),
            'message_error' => implode(', ', array_values((array)$body->params)),
            'status' => $response_status
        ], $response_status);
    }
    private function formatResponse($response, $message = '')
    {
        $body = json_decode($response->body());
        Log::info($response->body());
        $response_status = $response->status();

        if ($response_status >= 200 && $response_status < 300) {
            // Si la respuesta es un array, simplemente la devolvemos
            if (is_array($body)) {
                return response()->json(['data' => $body, 'status' => $response_status, 'message' => $message], $response_status);
            }

            // Si la respuesta es un objeto con un id, lo devolvemos
            if (isset($body->id)) {
                return response()->json(['id' => $body->id, 'status' => $response_status, 'message' => $message], $response_status);
            }

            // Si es un objeto sin id, devolvemos el objeto completo
            return response()->json(['data' => $body, 'status' => $response_status, 'message' => $message], $response_status);
        } else {
            // Manejo de errores cuando la respuesta no es 20x
            $params = isset($body->params) ? $body->params : null;
            $message_error = $params ? implode(', ', array_values((array)$params)) : '';
            return response()->json([
                'message' => $message . ' ' . $message_error,
                'message_error' => $message_error,
                'status' => $response_status
            ], $response_status);
        }
    }
    public function checkIfExistSku($internal_id)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->woocommerce_api_key . ':' . $this->woocommerce_api_secret),
        ])
            ->withoutVerifying()
            ->get($this->getProductUrl() . '?sku=' . $internal_id);

        if ($response->status() == 200) {
            $body = json_decode($response->body());
            if (!empty($body)) {
                $id = $body[0]->id;
                $woocommerce_item = WoocommerceItem::where('woocommerce_item_id', $id)->first();
                if (!$woocommerce_item) {
                    $item = Item::where('internal_id', $internal_id)->first();
                    WoocommerceItem::where('item_id', $item->id)->delete();
                    if ($item) {
                        WoocommerceItem::create([
                            'item_id' => $item->id,
                            'woocommerce_item_id' => $id
                        ]);
                    }
                }
            }
            return !empty($body);
        }
        return false;
    }
    public function checkIfExist($item_id)
    {
        return WoocommerceItem::where('item_id', $item_id)->exists();
    }
    private function desactiveSkuSync()
    {
        WoocommerceConfiguration::first()->update([
            'woocommerce_sku_sync' => false
        ]);
    }
    public function syncAllProducts()
    {

        $count_created = 0;
        $count_updated = 0;
        $to_create = [];
        $to_update = [];
        $failed = [];
        $response = [];
        // if ($this->checkSkuSync()) {
        //     $this->skuSync();
        //     $this->desactiveSkuSync();
        // }
        Item::where('active', true)->where('apply_store', true)->chunk(100, function ($items) use (&$count_created, &$count_updated, &$failed, &$to_create, &$to_update, &$response) {
            $item_map = [];

            foreach ($items as $item) {
                try {
                    if ($this->checkIfExistSku($item->internal_id)) {
                        $formatted = $this->formatItem($item, true);
                        $to_update[] = $formatted;
                    } else {
                        $formatted = $this->formatItem($item);
                        $to_create[] = $formatted;
                        $item_map[] = $item->id;
                    }
                } catch (Exception $e) {
                    $failed[] = ['id' => $item->id, 'message' => $e->getMessage()];
                }
            }
            $data = [
                'create' => $to_create,
                'update' => $to_update
            ];
            // $total = count($data['create']) + count($data['update']);
            // Log::info("enviando la cantidad de items: ".$total);
            $response = $this->syncProducts($data);
            if ($response->original['data'] ?? false) {
                $response = $response->original['data'];
                $create = isset($response->create) ? $response->create : [];
                $update = isset($response->update) ? $response->update : [];
                $count_created += count($create);
                $count_updated += count($update);

                foreach ($create as $index => $w_item) {
                    WoocommerceItem::create([
                        'item_id' => $item_map[$index],
                        'woocommerce_item_id' => $w_item->id
                    ]);
                }
            }
            $to_create = [];
            $to_update = [];
        });

        return [
            'success' => true,
            'message' => 'Sincronizado correctamente',
            'to_create' => $count_created,
            'to_update' => $count_updated,
        ];
    }
    private function syncProducts($items)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->woocommerce_api_key . ':' . $this->woocommerce_api_secret),
        ])
            ->withoutVerifying()
            ->post($this->getProductUrlBatch(), $items);

        return $this->formatResponseBatch($response, "Productos sincronizados");
    }
    private function getProductUrlBatch()
    {
        return $this->woocommerce_api_url . self::PRODUCT_BATCH_URL;
    }
    private function getProductUrl()
    {
        return $this->woocommerce_api_url . self::PRODUCT_URL;
    }

    private function checkSkuSync()
    {
        return WoocommerceConfiguration::first()->woocommerce_sku_sync;
    }
    private function skuSync()
    {
        Item::where('active', true)->where('apply_store', true)
            ->whereDoesntHave('woocommerce_item')
            ->chunk(100, function ($items) {
                foreach ($items as $item) {
                    $this->checkProduct($item);
                }
            });
    }

    private function authUrl()
    {
        return "/?consumer_key=" . $this->woocommerce_api_key . "&consumer_secret=" . $this->woocommerce_api_secret;
    }
    private function isEnabled()
    {
        return $this->woocommerce_api_url && $this->woocommerce_api_key && $this->woocommerce_api_secret;
    }
    public function getProductById($id)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->woocommerce_api_key . ':' . $this->woocommerce_api_secret),
        ])

            ->get($this->woocommerce_api_url . '/products/' . $id);
        $formatted = $this->formatResponse($response);
        return $formatted;
    }
    public function getAllItems()
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->woocommerce_api_key . ':' . $this->woocommerce_api_secret),
        ])
            ->withoutVerifying()
            ->get($this->getProductUrl());

        $response = $response->json();
        return $response;
    }
    public function checkProduct(Item $item)
    {
        $sku = $item->internal_id;
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->woocommerce_api_key . ':' . $this->woocommerce_api_secret),
        ])
            ->withoutVerifying()
            ->get($this->getProductUrl() . '?sku=' . $sku);
        if ($response->status() == 200) {
            $body = $response->json();
            if (count($body) > 0) {
                $id = $body[0]['id'];
                WoocommerceItem::create([
                    'item_id' => $item->id,
                    'woocommerce_item_id' => $id
                ]);
            }
            return true;
        }
        return false;
    }
    public function createProduct(Item $item)
    {
        $formated = $this->formatItem($item);
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->woocommerce_api_key . ':' . $this->woocommerce_api_secret),
        ])
            ->withoutVerifying()
            ->post($this->getProductUrl(), $formated);
        /** @var \Illuminate\Http\JsonResponse $formatted_response */
        $formatted_response = $this->formatResponse($response, "Producto creado");
        if ($formatted_response->getData()) {
            $id = $formatted_response->getData()->id;
            WoocommerceItem::create([
                'item_id' => $item->id,
                'woocommerce_item_id' => $id
            ]);
        }

        return $formatted_response;
    }

    public function updateProduct(Item $item)
    {
        
        $formated = $this->formatItem($item, true);
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->woocommerce_api_key . ':' . $this->woocommerce_api_secret),
        ])
            ->withoutVerifying()
            ->put($this->getProductUrl() . '/' . $item->woocommerce_item()
                ->where('woocommerce_item_id', '!=', 0)
                ->orderBy('id', 'desc')->first()->woocommerce_item_id, $formated);
        $formatted_response = $this->formatResponse($response, "Producto actualizado");
        return $formatted_response;
    }

    private function getWarehouseId()
    {
        $establishment_id = Establishment::find($this->user->establishment_id)->id;
        $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
        return $warehouse->id;
    }

    private function getStockByWarehouse($item_id)
    {
        $warehouse_id = $this->getWarehouseId();
        $item_warehouse = ItemWarehouse::where('item_id', $item_id)->where('warehouse_id', $warehouse_id)->first();
        if (!$item_warehouse) {
            return 0;
        }
        return floatval($item_warehouse->stock);
    }

    private function formatItem($item, $update = false)
    {

        $formated = [];
        if ($update) {
            $formated['id'] = $item->woocommerce_item()
                ->where('woocommerce_item_id', '!=', 0)
                ->orderBy('id', 'desc')
                ->first()->woocommerce_item_id;
        } else {
            $formated['type'] = 'simple';
            $formated['name'] = $item->name ?? $item->description;
            $formated['description'] = $item->description;
            $formated['short_description'] = $item->technical_specifications;
            $formated['sku'] = $item->internal_id;
        }
        $formated['regular_price'] = strval($item->sale_unit_price);
        $formated['price'] = strval($item->sale_unit_price);
        $stock = $this->getStockByWarehouse($item->id);
        $stock = $stock < 0 ? 0 : $stock;

        $formated['manage_stock'] = $stock > 0 ? true : false;
        $formated['stock_status'] = $stock > 0 ? 'instock' : 'outofstock';
        $formated['stock_quantity'] = $stock;

        return $formated;
        //         '{
        //   "name": "Premium Quality",
        //   "type": "simple",
        //   "regular_price": "21.99",
        //   "description": "Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.",
        //   "short_description": "Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.",
        //   "categories": [
        //     {
        //       "id": 9
        //     },
        //     {
        //       "id": 14
        //     }
        //   ],
        //   "images": [
        //     {
        //       "id": 42
        //     },
        //     {
        //       "src": "http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_back.jpg"
        //     }
        //   ]
        // }'

    }
}

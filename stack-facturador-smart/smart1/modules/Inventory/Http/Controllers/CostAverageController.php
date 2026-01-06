<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant\InitStock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Inventory\Models\CostAverage;
use Modules\Inventory\Models\InventoryKardex;

class CostAverageController extends Controller
{

    public function index()
    {
        return view('inventory::colors.index');
    }


    public function create()
    {
        return view('inventory::colors.form');
    }

    public function generate_cost(Request $request)
    {
        $this->get_init_stock($request);
        $last_average = CostAverage::where('item_id', $request->item_id)->latest('purchase_date')->first();
        $last_date = $last_average->purchase_date;
        $purchases = $this->get_purchases($request->item_id, $last_date);
    }
    function get_purchases($item_id, $last_date)
    {
        $purchases = [];
        $purchases = InventoryKardex::where('item_id', $item_id);
        $purchases = $purchases->where('inventory_kardexable_type', 'App\Models\Tenant\Purchase');
        $purchases = $purchases->where('date_of_issue', '>', $last_date);
        $purchases = $purchases->get();
        return $purchases;
    }


    function get_init_stock(Request $request)
    {
        $item_id = $request->item_id;
        $lastStock = InitStock::where('item_id', $item_id);
        $lastStock = $lastStock->whereNull("warehouse_id");
        $lastStock =   $lastStock->latest('init_date')->first();
        $lastDate = null;
        if ($lastStock) {
            $lastDate = $lastStock->init_date;
        }
        $stocks = [];



        if (!isset($lastDate)) {
            $init_date = InventoryKardex::where('item_id', $item_id);
            $init_date = $init_date->first();
            if (!isset($init_date)) {
                return ["success" => false, "msg" => "Error"];
            }
            $date = $init_date->date_of_issue;
            $init_stock = $init_date->quantity;

            $result = $this->get_all_dates($date);
            if (count($result) != 0) {
                foreach ($result as $key => $date) {
                    $last_date = $this->get_last_day($date);
                    $sum = InventoryKardex::where('item_id', $item_id);


                    $sum = $sum->whereBetween('date_of_issue', [$date, $last_date])
                        ->where(function ($query) use ($date) {
                            $query->where(function ($query) {
                                $query->where('inventory_kardexable_type', '<>', 'App\Models\Tenant\Document');
                            })
                                ->orWhere(function ($query) {
                                    $query->where('inventory_kardexable_type', 'App\Models\Tenant\Document')
                                        ->whereIn('inventory_kardexable_id', function ($query) {
                                            $query->select('id')
                                                ->from('documents')
                                                ->where(function ($query) {
                                                    $query->whereNull('sale_note_id')
                                                        ->orWhere('sale_note_id', '=', '')
                                                        ->where(function ($query) {
                                                            $query->whereNull('order_note_id')
                                                                ->orWhere('order_note_id', '=', '');
                                                        });
                                                });
                                        });
                                })->orWhere(function ($query) {
                                    $query->where('inventory_kardexable_type', 'App\Models\Tenant\SaleNote')
                                        ->whereIn('inventory_kardexable_id', function ($query) {
                                            $query->select('id')
                                                ->from('sale_notes')
                                                ->where(function ($query) {
                                                    $query->whereNull('order_note_id')
                                                        ->orWhere('order_note_id', '=', '');
                                                });
                                        });
                                });
                        })
                        ->sum("quantity");
                    if ($key != 0) {
                        $stocks[$date] = $stocks[$result[$key - 1]] + $sum;
                        $initStock = new InitStock([
                            'item_id' => $item_id,
                            'warehouse_id' => null,
                            'init_date' => $date,
                            'stock' => floatval($stocks[$result[$key - 1]]),
                        ]);
                        $initStock->save();
                    } else {
                        $stocks[$date] = floatval($sum);
                        $initStock = new InitStock([
                            'item_id' => $item_id,
                            'warehouse_id' => null,
                            'init_date' => $date,
                            'stock' => floatval($init_stock),
                        ]);
                        $initStock->save();
                    }
                }
            }
        } else {
            $result = $this->get_all_dates($lastDate->format("Y-m-d"));
            if (count($result) != 0) {
                foreach ($result as $key => $date) {
                    $last_date = $this->get_last_day($date);
                    $sum = InventoryKardex::where('item_id', $item_id);

                    $sum = $sum->whereBetween('date_of_issue', [$date, $last_date])
                        ->where(function ($query) use ($date) {
                            $query->where(function ($query) {
                                $query->where('inventory_kardexable_type', '<>', 'App\Models\Tenant\Document');
                            })
                                ->orWhere(function ($query) {
                                    $query->where('inventory_kardexable_type', 'App\Models\Tenant\Document')
                                        ->whereIn('inventory_kardexable_id', function ($query) {
                                            $query->select('id')
                                                ->from('documents')
                                                ->where(function ($query) {
                                                    $query->whereNull('sale_note_id')
                                                        ->orWhere('sale_note_id', '=', '')
                                                        ->where(function ($query) {
                                                            $query->whereNull('order_note_id')
                                                                ->orWhere('order_note_id', '=', '');
                                                        });
                                                });
                                        });
                                })->orWhere(function ($query) {
                                    $query->where('inventory_kardexable_type', 'App\Models\Tenant\SaleNote')
                                        ->whereIn('inventory_kardexable_id', function ($query) {
                                            $query->select('id')
                                                ->from('sale_notes')
                                                ->where(function ($query) {
                                                    $query->whereNull('order_note_id')
                                                        ->orWhere('order_note_id', '=', '');
                                                });
                                        });
                                });
                        })
                        ->sum("quantity");
                    if ($key != 0) {
                        $stocks[$date] = $stocks[$result[$key - 1]] + $sum;
                        $initStock = new InitStock([
                            'item_id' => $item_id,
                            'warehouse_id' => null,
                            'init_date' => $date,
                            'stock' => floatval($stocks[$result[$key - 1]]),
                        ]);
                        $initStock->save();
                    } else {
                        $stocks[$date] = floatval($sum) + floatval($lastStock->stock);
                    }
                }
            }
        }
    }
    function get_all_dates($init_date = null, $addMonth = false)
    {
        $fechaInicio = $init_date ?? "2021-01-01";
        $hoy = now();
        $fecha = Carbon::createFromFormat('Y-m-d', $fechaInicio);
        $primerosDias = [];
        while ($fecha <= $hoy) {

            $fecha = $fecha->firstOfMonth();
            if ($addMonth) {
                $fecha = $fecha
                    ->addMonth();
            }
            $primerosDias[] = $fecha->format('Y-m-d');
            $fecha->addMonthNoOverflow();
        }

        return $primerosDias;
    }

    function get_last_day($date)
    {

        $carbonFecha = Carbon::createFromFormat('Y-m-d', $date);

        $ultimoDiaMes = $carbonFecha->endOfMonth();

        return $ultimoDiaMes->format('Y-m-d'); // muestra '20
    }
}

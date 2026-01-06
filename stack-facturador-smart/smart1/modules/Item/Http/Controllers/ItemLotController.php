<?php

namespace Modules\Item\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Item\Models\ItemLot;
use Modules\Item\Http\Resources\ItemLotCollection;
use Modules\Item\Http\Resources\ItemLotResource;
use Modules\Item\Http\Requests\ItemLotRequest;
use Modules\Item\Exports\ItemLotExport;
use Carbon\Carbon;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\Document;
use App\Models\Tenant\SaleNote;


class ItemLotController extends Controller
{

    public function index()
    {
        return view('item::item-lots.index');
    }


    public function columns()
    {
        return [
            'series' => 'Serie',
            'date' => 'Fecha',
            'state' => 'Estado',
            'item_description' => 'Producto',
            'date_due' => 'Fecha de vencimiento',
        ];
    }
    public function historyLots($id)
    {
        $register_lot = ItemLot::findOrFail($id);
        if(!$register_lot){
            return response()->json(['error' => 'Serie no encontrada'], 404);
        }
        $item_id = $register_lot->item_id;
        $serie = $register_lot->series;

        // Buscar compras que contengan la serie en sus items
        $purchases = Purchase::whereHas('items', function($query) use ($item_id, $serie) {
            $query->where('item_id', $item_id)
                  ->whereRaw("JSON_SEARCH(JSON_EXTRACT(item, '$.lots[*].series'), 'one', ?) IS NOT NULL", [$serie]);
        })->get();

        // Buscar comprobantes de venta que contengan la serie en sus items
        $documents = Document::whereHas('items', function($query) use ($item_id, $serie) {
            $query->where('item_id', $item_id)
                  ->whereRaw("JSON_SEARCH(JSON_EXTRACT(item, '$.lots[*].series'), 'one', ?) IS NOT NULL", [$serie]);
        })->get();

        // Buscar notas de venta que contengan la serie en sus items
        $sale_notes = SaleNote::whereHas('items', function($query) use ($item_id, $serie) {
            $query->where('item_id', $item_id)
                  ->whereRaw("JSON_SEARCH(JSON_EXTRACT(item, '$.lots[*].series'), 'one', ?) IS NOT NULL", [$serie]);
        })->get();

        return response()->json([
            'success' => true,
            'data' => [
                'purchases' => $purchases,
                'documents' => $documents,
                'sale_notes' => $sale_notes
            ]
        ]);
    }

    public function records(Request $request)
    {

        $records = $this->getRecords($request);

        return new ItemLotCollection($records->paginate(config('tenant.items_per_page')));
    }


    public function getRecords($request)
    {
        $records = ItemLot::query();
        if ($request->column == 'item_description') {

            $records = $records->whereHas('item', function ($query) use ($request) {
                $query->where('description', 'like', "%{$request->value}%")->latest();
            });
        } else if ($request->column == 'date_due' && $request->value !== null) {
            $days = (int)$request->value;
            if($days == 0){
                $records = $records->where('date', '<=', Carbon::now()->startOfDay())->latest();
            }else{
                $start_date = Carbon::now()->startOfDay();
                $end_date = Carbon::now()->addDays($days)->endOfDay();
                $records = $records->whereBetween('date', [$start_date, $end_date])->latest();
            }
        
        } else {
            if ($request->column !== 'date_due') {
                $records = $records->where($request->column, 'like', "%{$request->value}%")->latest();
            } else {
                $records = $records->latest();
            }
        }

        return $records;
    }


    public function record($id)
    {
        $record = ItemLot::findOrFail($id);

        return $record;
    }


    public function store(ItemLotRequest $request)
    {

        $id = $request->input('id');
        $record = ItemLot::findOrFail($id);
        $record->series = $request->series;
        $record->save();

        return [
            'success' => true,
            'message' => ($id) ? 'Serie editada con éxito' : 'Serie registrada con éxito',
        ];
    }

    public function export(Request $request)
    {

        $records = $this->getRecords($request)->get();

        return (new ItemLotExport)
            ->records($records)
            ->download('Series_' . Carbon::now() . '.xlsx');
    }

    public function checkSeries(Request $request)
    {
        $series = $request->input('lots');
        $item_id = $request->input('item_id');
        $series_exist = [];
        foreach ($series as $value) {
            $series = $value['series'];
            $serie_exist = ItemLot::where('series', $series)->where('item_id', $item_id)->first();
            if ($serie_exist) {
                array_push($series_exist, $serie_exist->series);
            }
        }
        return $series_exist;
    }
}

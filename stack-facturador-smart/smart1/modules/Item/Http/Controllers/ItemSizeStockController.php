<?php

namespace Modules\Item\Http\Controllers;

use App\Models\Tenant\ItemSizeStock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Item\Imports\ItemSetIndividualImport;
use Modules\Item\Imports\ItemSetImport;
use Maatwebsite\Excel\Excel;
use Modules\Item\Exports\ItemSizeExport;
use Modules\Item\Http\Resources\ItemSizeCollection;

class ItemSizeStockController extends Controller
{


   
    public function index()
    {
        return view('item::item-sizes.index');
    }
    public function columns()
    {
        return [
            'size' => 'Talla',
            'item_description' => 'Producto',
        ];
    }
    public function export(Request $request)
    {

        $records = $this->getRecords($request)->get();

        return (new ItemSizeExport)
                ->records($records)
                ->download('Tallas_'.Carbon::now().'.xlsx');

    }

    public function updateSize(Request $request,$id){
        $itemSize = ItemSizeStock::findOrFail($id);
        //checa si la talla ya existe, que no tenga el mismo id pero si el item_id
        $itemSizeExist = ItemSizeStock::where('size',$request->size)->where('item_id',$itemSize->item_id)
        ->where('id','!=',$id)
        ->first();
        if($itemSizeExist){
            return response()->json(['success' => false, 'message' => 'La talla ya existe']);
        }
        $itemSize->size = $request->size;
        $itemSize->save();
        return response()->json(['success' => true, 'message' => 'Talla actualizada']);
    }
    public function getRecords($request)
    {

        if ($request->column == 'item_description') {

            $records = ItemSizeStock::whereHas('item', function ($query) use ($request) {
                $query->where('description', 'like', "%{$request->value}%")->latest();
            });
        } else {
            $records = ItemSizeStock::where($request->column, 'like', "%{$request->value}%");
        }

        return $records;
    }

    public function records(Request $request)
    {

        $records = $this->getRecords($request);

        return new ItemSizeCollection($records->paginate(config('tenant.items_per_page')));
    }
}

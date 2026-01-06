<?php

namespace Modules\Item\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Item\Http\Requests\WebPlatformRequest;
use Modules\Item\Models\StateDelivery;

class StatesDeliveryController extends Controller
{

    public function index()
    {
        return view('item::web-platforms.index');
    }

    public function records()
    {
        return StateDelivery::get();
    }
 

    public function record($id)
    {
        $record = StateDelivery::findOrFail($id);

        return $record;
    }

    
    public function store(WebPlatformRequest $request)
    {
        $id = $request->input('id');
        $record = StateDelivery::firstOrNew(['id' => $id]);
        $record->fill($request->all());
        $record->save();

        return [
            'success' => true,
            'message' => ($id)? 'Estado de entrega editado con éxito':'Estado de entrega registrada con éxito'
        ];
    }


    public function destroy($id)
    {
            
        $record = StateDelivery::findOrFail($id);
        $record->delete(); 

        return [
            'success' => true,
            'message' => 'Estado de entrega eliminada con éxito'
        ];

    }


}

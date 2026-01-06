<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\CurrencyTypeRequest;
use App\Http\Resources\Tenant\CurrencyTypeCollection;
use App\Http\Resources\Tenant\CurrencyTypeResource;
use App\Http\Resources\Tenant\MessageIntegrateSystemCollection;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\MessageIntegrateSystem;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class MessageIntegrateSystemController extends Controller
{
    public function index(){

        return view('tenant.message_integrate_system.index');
    }
    public function records()
    {
        $records = MessageIntegrateSystem::query();

        return new MessageIntegrateSystemCollection($records->paginate(config('tenant.items_per_page')));
    }

    public function columns(){

        return [
            'trigger_event' => 'Evento',
            'comment' => 'Comentario',
            'message' => 'Mensaje',
        ];
    }
    public function record($id)
    {
        $record = MessageIntegrateSystem::findOrFail($id);

        return $record;
    }

    public function store(Request $request)
    {
        $id = $request->input('id');
        $currency_type = MessageIntegrateSystem::firstOrNew(['id' => $id]);
        $currency_type->fill($request->all());
        $currency_type->save();

        return [
            'success' => true,
            'message' => ($id)? 'Mensaje editado con éxito': 'Mensaje registrado con éxito'
        ];
    }

}
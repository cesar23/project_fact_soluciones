<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Mail\Tenant\IntegrateSystemEmail;
use App\Models\Tenant\Agency;
use App\Models\Tenant\AgencyDispatchTable;
use App\Models\Tenant\DispatchOrder;
use App\Models\Tenant\Person;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Finance\Helpers\UploadFileHelper;

class AgencyController extends Controller
{

    public function records()
    {
        $records = Agency::all();
        return $records;
    }

    public function columns()
    {
        return [
            'description' => 'Descripción',
            'active' => 'Estado'
        ];
    }
    public function record($id)
    {
        $record = Agency::findOrFail($id);
        return $record;
    }
    public function deleteAgencyDispatch($id)
    {
        $dispatchOrder = DispatchOrder::find($id);
        $agency_dispatch = AgencyDispatchTable::where('dispatch_order_id', $dispatchOrder->id)->first();
        $filePath = $agency_dispatch->dispatch_file;
        $adjustedPath = str_replace('storage/', 'public/', $filePath);

        if (Storage::exists($adjustedPath)) {
            Storage::delete($adjustedPath);
        }
        $agency_dispatch->delete();

        return [
            'success' => true,
            'message' => 'Archivo eliminado con éxito'
        ];
    }
    public function getAgencyDispatch($id)
    {
        $dispatchOrder = DispatchOrder::find($id);
        $agency_dispatch = AgencyDispatchTable::where('dispatch_order_id', $dispatchOrder->id)->first();
        $image = $agency_dispatch->dispatch_file;
        if ($image) {
            $url = asset($image);
            $agency_dispatch->dispatch_file = $url;
        }

        return $agency_dispatch;
    }
    public function saveAgencyDispatch(Request $request)
    {
        $dispatch_order_id = $request->dispatch_order_id;
        $dispatch_order = DispatchOrder::find($dispatch_order_id);
        //en la request viene un archivo en el campo image,
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $request->validate(['file' => 'mimes:jpeg,png,jpg|max:1024']);
            $file = $request->file('image');
            $ext = $file->getClientOriginalExtension();
            $filename = time() . '.' . $ext;
            UploadFileHelper::checkIfValidFile($filename, $file->getPathName(), true);
            //guardamos el archivo en el tenant en una carpeta llamada 'agency_dispatch

            $file->storeAs('public/uploads/agency_dispatch', $filename);
            $path = 'storage/uploads/agency_dispatch/' . $filename;
            //obtenemos la ruta del archivo guardado
            $url = asset($path);
            $customer = Person::find($dispatch_order->customer_id);
            $customer_email = $customer->email;
            $message = "Estimado cliente, le enviamos los datos de la agencia de transporte.";

            if ($message != '' && $customer_email) {

                $mailable = new IntegrateSystemEmail($customer, $message, $url);
                $id = $dispatch_order->id;
                EmailController::SendMail($customer_email, $mailable, $id, 6);
            }
        }
        //guardamos la ruta en la base de datos
        $agency_dispatch_table = AgencyDispatchTable::create([
            'date_of_issue' => Carbon::now()->format('Y-m-d'),
            'dispatch_order_id' => $request->dispatch_order_id,
            'dispatch_file' => $path
        ]);
        return [
            'success' => true,
            'data' => $agency_dispatch_table,
            'message' => 'Registrado con éxito'
        ];
    }
    public function store(Request $request)
    {

        $id = $request->input('id');
        $agency = Agency::firstOrNew(['id' => $id]);
        $agency->fill(request()->all());
        $agency->save();

        return [
            'success' => true,
            'data' => $agency,
            'message' => ($id) ? 'Agencia editada con éxito' : 'Agencia registrada con éxito'
        ];
    }
}

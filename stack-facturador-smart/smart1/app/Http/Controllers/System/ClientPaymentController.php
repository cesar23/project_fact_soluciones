<?php
namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\ClientPaymentRequest;
use App\Http\Requests\System\DocumentRequest;
use App\Http\Resources\System\ClientPaymentCollection;
use App\Models\System\Client;
use App\Models\System\ClientPayment;
use App\Models\System\PaymentMethodType;
use App\Models\System\CardBrand;
use Hyn\Tenancy\Environment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;



class ClientPaymentController extends Controller
{
    public function records($client_id)
    {
        $records = ClientPayment::where('client_id', $client_id)->get();

        return new ClientPaymentCollection($records);
    }
    public function delete_file_payment($payment_id){
        $payment = ClientPayment::findOrFail($payment_id);
        $file_name = $payment->payment_file;
        //delete file
        $path = 'app/public/uploads/payments/';
        unlink(storage_path($path.$file_name));
        $payment->payment_file = null;
        $payment->save();
        return [
            'success' => true,
            'message' => 'Archivo eliminado correctamente',
            'name' => $file_name
        ];
  

    }
    public function store_file_payment($payment_id,Request $request){
        $payment = ClientPayment::findOrFail($payment_id);
        $client = Client::findOrFail($payment->client_id);
        $file = $request->file('file');
        //create original name with extension 
        $name =$client->number."_".$payment->id."_".$file->getClientOriginalName();
        
        $path = 'app/public/uploads/payments/';
        $file->move(storage_path($path), $name);
        $payment->payment_file = $name;
        $payment->save();
        return [
            'success' => true,
            'message' => 'Archivo subido correctamente',
            'name' => $name
        ];

    }
    public function tables()
    {
        return [
            'payment_method_types' => PaymentMethodType::all(),
            'card_brands' => CardBrand::all()
        ];
    }

    public function client($client_id)
    {
        $client = Client::find($client_id);

        $total_paid = collect($client->payments)->where('state',true)->sum('payment');
        $total = collect($client->payments)->sum('payment');
        $total_difference = round($total - $total_paid, 2);

        return [
            'name' => $client->name,
            'pricing' => $client->plan->pricing,
            'total_paid' => $total_paid,
            'total' => $total,
            'total_difference' => $total_difference
        ];

    }

    public function store(ClientPaymentRequest $request)
    {
        $id = $request->input('id');
        $record = ClientPayment::firstOrNew(['id' => $id]);
        $record->fill($request->all());
        $record->save();

        $client = Client::findOrFail($request->client_id);
        $tenancy = app(Environment::class);
        $tenancy->tenant($client->hostname->website);

        DB::connection('tenant')->table('account_payments')->insert(

            ['date_of_payment' => $record->date_of_payment, 'reference_id' => $record->id, 'payment_method_type_id'=> $record->payment_method_type_id, 'card_brand_id' =>$record->card_brand_id, 'reference' => $record->reference, 'payment' => $record->payment, 'state' => 0, 'created_at' => date('Y-m-d H:i:s')]
        );

        return [
            'success' => true,
            'message' => ($id)?'Pago editado con éxito':'Pago programado con éxito'
        ];
    }

    public function destroy($id)
    {
        $item = ClientPayment::findOrFail($id);
        $item->delete();

        return [
            'success' => true,
            'message' => 'Pago eliminado con éxito'
        ];
    }

    
    public function cancel_payment($client_payment_id)
    {
        $client_payment = ClientPayment::find($client_payment_id);
        $client_payment->state = true;
        $client_payment->save();

        $client = Client::findOrFail($client_payment->client_id);
        $tenancy = app(Environment::class);
        $tenancy->tenant($client->hostname->website);
        DB::connection('tenant')->table('account_payments')->where('reference_id', $client_payment->id)->update(['state' => 1, 'date_of_payment_real' => date('Y-m-d')]);

        return [
            'success' => true,
            'message' => 'Monto pagado', 
        ];

    } 
    public function uploadPaymentFiles(Request $request)
    {
        $paymentId = $request->input('paymentId');
        $clientPayment = ClientPayment::findOrFail($paymentId);
        $photos = json_decode($clientPayment->photos, true) ?? [];
    
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $fileName = $file->getClientOriginalName();
                $file->move(public_path('payments'), $fileName);
                $photos[] = $fileName; // Añadir solo el nombre del archivo al array de fotos
            }
    
            // Actualizar la columna photos en la base de datos
            $clientPayment->photos = json_encode($photos);
            $clientPayment->save();
        }
    
        return response()->json(['message' => 'Archivos subidos correctamente.']);
    }
    
    
    
}

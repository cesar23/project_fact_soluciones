<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\MassiveMessageCollection;
use App\Http\Resources\Tenant\PersonCollection;
use App\Http\Resources\Tenant\PersonLiteCollection;
use App\Jobs\SendMassiveMessage;
use App\Models\Tenant\MassiveMessage;
use App\Models\Tenant\Person;
use App\Traits\JobReportTrait;
use Illuminate\Http\Request;
use App\Models\Tenant\MassiveMessageDetail;
use App\Models\Tenant\Company;
use App\Http\Controllers\Tenant\WhatsappController;
use App\Http\Resources\Tenant\MassiveMessageDetailCollection;

class MassiveMessageController extends Controller

{
    use JobReportTrait;
    public function index()
    {
        $company = Company::first();
        $hasGekawa = $company->gekawa_1 && $company->gekawa_2 && $company->gekawa_url;

        return view('tenant.massive_message.index', compact('hasGekawa'));
    }

    public function store(Request $request)
    {
        $message = MassiveMessage::updateOrCreate(['id' => $request->id], $request->all());
        return response()->json(['success' => true, 'message' => $message->id ? 'Mensaje masivo actualizado correctamente' : 'Mensaje masivo creado correctamente']);
    }

    public function sendMessageQuery(Request $request, $id){
        $website = $this->getTenantWebsite();
        
        $search  = $request->search;

        SendMassiveMessage::dispatch($id, $search, $website);
        // $message->sendMessage($request->ids);
        return response()->json(['success' => true, 'message' => 'Mensaje enviado correctamente']);
    }
    public function sendMessage(Request $request, $id)
    {

        $website = $this->getTenantWebsite();
        
        $person_ids = $request->ids;

        SendMassiveMessage::dispatch($id, $person_ids, $website);
        // $message->sendMessage($request->ids);
        return response()->json(['success' => true, 'message' => 'Mensaje enviado correctamente']);
    }
    public function records(Request $request)
    {
        $column = $request->column;
        $value = $request->value;
        $records = MassiveMessage::query();
        if ($column && $value) {
            $records->where($column, 'like', '%' . $value . '%');
        }
        return new MassiveMessageCollection($records->paginate(30));
    }
    public function columns()
    {
        return [
            'subject' => 'Asunto',
        ];
    }

    public function persons(Request $request)
    {
        $search = $request->search;
        $persons = Person::whereNotNull('telephone')->where('type', 'customers');
        if ($search) {
            $persons->where('name', 'like', '%' . $search . '%')
                ->orWhere('number', 'like', '%' . $search . '%');
        }
        return new PersonLiteCollection($persons->paginate(20));
    }

    public function show($id)
    {
        $message = MassiveMessage::findOrFail($id);
        return response()->json($message);
    }

    public function update(Request $request, $id)
    {
        $message = MassiveMessage::findOrFail($id);
        $message->update($request->all());
        return response()->json($message);
    }

    public function destroy($id)
    {
        $message = MassiveMessage::findOrFail($id);
        $message->delete();
        return response()->json(['success' => true, 'message' => 'Mensaje eliminado correctamente']);
    }

    public function history(Request $request, $id)
    {
        $query = MassiveMessageDetail::with('person')
            ->where('massive_message_id', $id);

        // Filtro por fecha
        if ($request->has('date_range') && is_array($request->date_range)) {
            $query->whereBetween('last_attempt_at', [
                $request->date_range[0] . ' 00:00:00',
                $request->date_range[1] . ' 23:59:59'
            ]);
        }

        // Filtro por cliente
        if ($request->has('customer') && !empty($request->customer)) {
            $query->whereHas('person', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->customer . '%')
                  ->orWhere('number', 'like', '%' . $request->customer . '%');
            });
        }

        // Filtro por estado
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        $records = $query->orderBy('last_attempt_at', 'desc');
                        

        return new MassiveMessageDetailCollection($records->paginate(30));
    }

    public function resend(Request $request, $messageId, $detailId)
    {
        $detail = MassiveMessageDetail::findOrFail($detailId);
        
        try {
            $company = Company::first();
            $request = new Request([
                'appkey' => $company->gekawa_1,
                'authkey' => $company->gekawa_2,
                'to' => "+51" . $detail->person->telephone,
                'message' => $detail->message,
                'gekawa_url' => $company->gekawa_url,
            ]);
            
            $response = (new WhatsappController)->sendWhatsappMessageSimple($request);
            $data = $response->getData();
            
            if ($data->success) {
                $detail->status = 'sent';
                $detail->attempts += 1;
                $detail->last_attempt_at = now();
                $detail->save();
                
                return response()->json(['success' => true, 'message' => 'Mensaje reenviado correctamente']);
            }
            
            $detail->status = 'failed';
            $detail->attempts += 1;
            $detail->last_attempt_at = now();
            $detail->save();
            
            return response()->json(['success' => false, 'message' => 'Error al reenviar el mensaje'], 422);
            
        } catch (\Exception $e) {
            $detail->status = 'failed';
            $detail->attempts += 1;
            $detail->last_attempt_at = now();
            $detail->error_message = $e->getMessage();
            $detail->save();
            
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\DocumentRecurrenceCollection;
use App\Http\Resources\Tenant\DocumentRecurrenceItemCollection;
use App\Http\Resources\Tenant\DocumentRecurrenceResource;
use App\Models\Tenant\Document;
use App\Models\Tenant\DocumentRecurrence;
use App\Models\Tenant\DocumentRecurrenceItem;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DocumentRecurrenceController extends Controller
{

    public function index()
    {
        return view('tenant.document_recurrence.index');
    }
    public function record($id)
    {

        $document_recurrence = DocumentRecurrence::findOrFail($id);

        return new DocumentRecurrenceResource($document_recurrence);
    }
    public function columns()
    {
        return [
            'emission_date' => 'Fecha de emisión',
            'emission_time' => 'Hora de emisión',
            'interval' => 'Intervalo',
            'emitted' => 'Emitido',
            'send_email' => 'Enviar email',
            'send_whatsapp' => 'Enviar whatsapp',
        ];
    }

    public function updateRecurrenceEmission(Request $request)
    {
        $id = $request->id;

        $document_recurrence_item = DocumentRecurrenceItem::findOrFail($id);
        $document_recurrence_item->fill($request->all());
        $document_recurrence_item->save();

        return [
            'success' => true,
            'message' => 'Emisión actualizada correctamente'
        ];
    }
    public function recordsRecurrenceEmitted(Request $request)
    {
        $emitted = $request->emitted === 'true' ? true : false;
        $recurrence_id = $request->recurrence_id;
        $records = DocumentRecurrenceItem::where('document_recurrence_id', $recurrence_id)
            ->where('emitted', $emitted);
        $page =    $emitted ? config('tenant.items_per_page') : 5;
        return new DocumentRecurrenceItemCollection($records->paginate($page));
    }
    public function records(Request $request)
    {
        $records = DocumentRecurrence::query();

        return new DocumentRecurrenceCollection($records->paginate(config('tenant.items_per_page')));
    }
    public function store(Request $request)
    {
        $id = $request->id;
        if (!$id) {
            $exists = DocumentRecurrence::where('document_id', $request->document_id)->exists();
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe una recurrencia para este documento'], 200);
            }
        }
        $documentId = $request->document_id;
        $initialDate = Carbon::parse($request->initial_date);
        $initialTime = $request->initial_time;
        $interval = $request->interval;
        $send_whatsapp = $request->send_whatsapp ?? false;
        $send_email = $request->send_email ?? false;
        $document_recurrence = DocumentRecurrence::firstOrNew(['id' => $id]);
        DocumentRecurrenceItem::where('document_recurrence_id', $id)->where('emitted', false)->delete();
        $document_recurrence->document_id = $documentId;
        $document_recurrence->interval = $interval;
        $document_recurrence->send_whatsapp = $send_whatsapp;
        $document_recurrence->send_email = $send_email;
        $document_recurrence->save();

        for ($i = 0; $i < 10; $i++) {
            if ($interval === 'daily') {
                $nextDate = $initialDate->copy()->addDays($i);
            } elseif ($interval === 'weekly') {
                $nextDate = $initialDate->copy()->addWeeks($i);
            } elseif ($interval === 'biweekly') {
                $nextDate = $initialDate->copy()->addWeeks(2 * $i);
            } elseif ($interval === 'monthly') {
                $nextDate = $initialDate->copy()->addMonths($i);
            } elseif ($interval === 'bimonthly') {
                $nextDate = $initialDate->copy()->addMonths(2 * $i);
            } elseif ($interval === 'quarterly') {
                $nextDate = $initialDate->copy()->addMonths(3 * $i);
            } elseif ($interval === 'semiannual') {
                $nextDate = $initialDate->copy()->addMonths(6 * $i);
            } elseif ($interval === 'annual') {
                $nextDate = $initialDate->copy()->addYears($i);
            } else {
                $nextDate = $initialDate->copy();
            }


            DocumentRecurrenceItem::create([
                'document_recurrence_id' => $document_recurrence->id,
                'emission_date' => $nextDate,
                'emission_time' => $initialTime,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Emisiones creadas correctamente']);
    }
    public function destroy($id)
    {
        try {
            $recurrence = DocumentRecurrence::findOrFail($id);
            
            // Eliminar los items de recurrencia primero
            DocumentRecurrenceItem::where('document_recurrence_id', $id)
                ->delete();
                
            // Eliminar la recurrencia
            $recurrence->delete();

            return [
                'success' => true,
                'message' => 'Recurrencia eliminada con éxito'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar la recurrencia: ' . $e->getMessage()
            ];
        }
    }
}

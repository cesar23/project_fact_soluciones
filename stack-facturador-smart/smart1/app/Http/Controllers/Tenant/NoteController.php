<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\Document;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Note;
use App\Models\Tenant\SaleNote;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function create($document_id)
    {
        $document_affected = Document::with(['items','invoice'])->find($document_id);
        $configuration = Configuration::getConfig();

        return view('tenant.documents.note', compact('document_affected', 'configuration'));
    }
    public function createOther()
    {
        $api_token = \App\Models\Tenant\Configuration::getApiServiceToken();
        $configuration = Configuration::getConfig();

        return view('tenant.documents.note_other', compact('configuration', 'api_token'));
    }

    public function createNv($sale_note_id){
        $sale_note_affected = SaleNote::with(['items'])->find($sale_note_id);
        $configuration = Configuration::getConfig();

        return view('tenant.documents.note_nv', compact('configuration', 'sale_note_affected'));
    }
    public function record($document_id)
    {
        $record = Document::find($document_id);

        return $record;
    }

    public function hasDocuments($document_id)
    {

        $record = Document::wherehas('affected_documents')->find($document_id);

        if ($record) {

            return [
                'success' => true,
                'data' => $record->affected_documents->transform(function ($row, $key) {
                    return [
                        'id' => $row->id,
                        'document_id' => $row->document_id,
                        'document_type_description' => $row->document->document_type->description,
                        'description' => $row->document->number_full,
                    ];
                })
            ];
        }

        return [
            'success' => false,
            'data' => []
        ];
    }

    public function searchNoUsed(Request $request)
    {
        $input = $request->input('input');
        $currencies = CurrencyType::all();
        $notes = Note::where('is_used', false)->whereHas('document', function($query) use ($input){
            $query->where('number', 'like', '%' . $input . '%');
        })->get()->transform(function($row) use ($currencies) {
            $description = $row->document->series . '-' . $row->document->number.' '.$currencies->where('id', $row->document->currency_type_id)->first()->symbol.' '.number_format($row->document->total, 2, '.', '');
            return [
                'id' => $row->id,
                'number' => $row->document->series . '-' . $row->document->number,
                'total' => $row->document->total,
                'date' => $row->document->date_of_issue,
                'description' => $description,
            ];
        });
        return response()->json($notes);
    }
}

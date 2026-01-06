<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\DocumentColumn;
use App\Models\Tenant\FontToDocumentsPdf;
use Illuminate\Http\Request;

class DocumentColumnController extends Controller
{


    public function index()
    {

        return view('tenant.document_columns.index');
    }
    public function updateTwoRecords(Request $request)
    {
        $id_current = $request->input('id_current');
        $column_order_current = $request->input('column_order_current');
        $id = $request->input('id');
        $column_order = $request->input('column_order');

        $record_current = DocumentColumn::find($id_current);
        $record_current->column_order = $column_order_current;
        $record_current->save();

        $record = DocumentColumn::find($id);
        $record->column_order = $column_order;
        $record->save();

        return response()->json(['success' => true]);
    }
    public function getFontSize()
    {
        $font_size = FontToDocumentsPdf::where('document_type', 'quotation')->where('format', 'a4')->first();
        return response()->json(['font_size' => $font_size->font_size]);
    }
    public function updateFontSize(Request $request)
    {
        $font_size = $request->input('font_size');
        $font_size_record = FontToDocumentsPdf::where('document_type', 'quotation')->where('format', 'a4')->first();
        $font_size_record->font_size = $font_size;
        $font_size_record->save();
        return response()->json(['success' => true]);
    }
    private function cloneDocColumns($type)
    {
        $docRecords = DocumentColumn::where('type', 'DOC')->get();

        $newRecords = $docRecords->map(function ($docRecord) use ($type) {
            return [
                'value' => $docRecord->value,
                'name' => $docRecord->name,
                'width' => $docRecord->width,
                'order' => $docRecord->order,
                'is_visible' => $docRecord->is_visible,
                'column_align' => $docRecord->column_align,
                'column_order' => $docRecord->column_order,
                'type' => $type,
            ];
        })->toArray();

        DocumentColumn::insert($newRecords);
    }
    public function records(Request $request)
    {
        $type = $request->input('type') ?? 'DOC';
        $exists = DocumentColumn::where('type', $type)->exists();
        if (!$exists) {
            $this->cloneDocColumns($type);
        }
        $records = DocumentColumn::where('type', $type)->orderBy('column_order', 'asc')->get();

        return $records;
    }

    public function updateTwoProperties(Request $request)
    {
        $id = $request->input('id');
        $property_1 = $request->input('property_1');
        $property_2 = $request->input('property_2');
        $value_1 = $request->input('value_1');
        $value_2 = $request->input('value_2');
        $record = DocumentColumn::find($id);
        $record->$property_1 = $value_1;
        $record->$property_2 = $value_2;
        $record->save();

        return response()->json(['success' => true]);
    }

    public function record($id)
    {
        $record = DocumentColumn::findOrFail($id);
        return $record;
    }


    public function store(Request $request)
    {

        $id = $request->input('id');
        $agency = DocumentColumn::firstOrNew(['id' => $id]);
        $agency->fill(request()->all());
        $agency->save();

        return [
            'success' => true,
            'data' => $agency,
            'message' => ($id) ? 'Agencia editada con éxito' : 'Agencia registrada con éxito'
        ];
    }
}

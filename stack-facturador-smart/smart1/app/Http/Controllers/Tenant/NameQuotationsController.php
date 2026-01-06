<?php

namespace App\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tenant\NameQuotations;

class NameQuotationsController extends Controller
{
    public function record()
    {
        $name_document = NameQuotations::first();
        return [
            'success' => true,
            'data' => $name_document
        ];
    }
    public function store(Request $request)
    {
        $id = $request->input('id');
        $name_document = NameQuotations::findOrNew($id);
        $name_document->quotations_optional = $request->input('quotations_optional');
        $name_document->quotations_optional_value = $request->input('quotations_optional_value');
        $name_document->save();
        return [
            'success' => true,
            'message' => 'Se guardo correctamente'
        ];
    }
}

<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\NameDocument;
use Illuminate\Http\Request;



class NameDocumentController extends Controller
{
    public function record()
    {
        $name_document = NameDocument::first();
        return [
            'success' => true,
            'data' => $name_document
        ];
    }
    public function store(Request $request)
    {
        $id = $request->input('id');
        $sale_note = $request->input('sale_note');
        $orden_note = $request->input('orden_note');
        $quotation = $request->input('quotation');
        $sale_opportunity = $request->input('sale_opportunity');
        $technical_service = $request->input('technical_service');
        $contract = $request->input('contract');
     

        $name_document = NameDocument::findOrNew($id);
        $name_document->sale_note = $sale_note;
        $name_document->orden_note = $orden_note;
        $name_document->quotation = $quotation;
        $name_document->sale_opportunity = $sale_opportunity;
        $name_document->technical_service = $technical_service;
        $name_document->contract = $contract;
  

        $name_document->save();
        return [
            'success' => true,
            'message' => 'Se guardo correctamente'
        ];
    }
}

<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\MassiveMessageDetail;
use Illuminate\Http\Request;

class MassiveMessageDetailController extends Controller
{
    public function index()
    {
        $details = MassiveMessageDetail::all();
        return response()->json($details);
    }

    public function store(Request $request)
    {
        $detail = MassiveMessageDetail::create($request->all());
        return response()->json($detail, 201);
    }

    public function show($id)
    {
        $detail = MassiveMessageDetail::findOrFail($id);
        return response()->json($detail);
    }

    public function update(Request $request, $id)
    {
        $detail = MassiveMessageDetail::findOrFail($id);
        $detail->update($request->all());
        return response()->json($detail);
    }

    public function destroy($id)
    {
        $detail = MassiveMessageDetail::findOrFail($id);
        $detail->delete();
        return response()->json(null, 204);
    }
}

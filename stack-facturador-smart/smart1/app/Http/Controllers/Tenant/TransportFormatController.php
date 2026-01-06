<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\TransportFormatCollection;
use App\Models\Tenant\TransportFormat;
use Illuminate\Http\Request;

class TransportFormatController extends Controller
{
    public function records(Request $request)
    {
        $records = TransportFormat::orderBy('id', 'desc')
            ->paginate(20);

        return new TransportFormatCollection($records);
    }
} 
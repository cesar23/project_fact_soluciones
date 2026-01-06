<?php

namespace Modules\Account\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Exception;
use App\Http\Resources\System\ClientCollection;
use Hyn\Tenancy\Environment;
use App\Models\System\SubDiary;
use Illuminate\Support\Facades\DB;
use App\Models\System\Configuration;
use App\Models\System\LedgerAccount;
use App\Models\System\SubDiaryItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Account\Http\Controllers\AccountController;
use Modules\Account\Http\Resources\System\LedgerAccountCollection;
use Modules\Account\Http\Resources\System\SubdiaryCollection;
use Modules\Account\Models\Account;

class SystemSubdiariesController extends Controller
{

    public function index()
    {
        return view('account::system.accounting.subdiaries');
    }


    public function records()
    {
        $records = SubDiary::latest()->get();
        return new SubdiaryCollection($records);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $date = Carbon::now()->format('Y-m-d');
            $code = $request->input('code');
            $exists = SubDiary::where('code', $code)->first();
            if ($exists) {
                return response()->json(['message' => 'El código del subdiario ya existe', 'success' => false], 200);
            }
            $request->merge(['date' => $date]);
            $subdiary = SubDiary::create($request->all());

            $items = $request->input('accounts');
            foreach ($items as $item) {
                $item['sub_diary_code'] = $subdiary->code;
                $item['description'] = $item['name'];
                $item['correlative_number'] = '000000';
                $item['debit'] = $item['movement'] == 'debit';
                $item['credit'] = $item['movement'] == 'credit';
                $item['debit_amount'] = 0;
                $item['credit_amount'] = 0;
                $item['document_number'] = 'F000-000000';
                SubDiaryItem::create($item);
            }
            DB::commit();
            return response()->json(['message' => 'Subdiario creado correctamente', 'success' => true]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            DB::rollBack();
            return response()->json(['message' => 'Ocurrió un error al crear el subdiario', 'success' => false], 500);
        }
    }

    public function accounts(Request $request)
    {
        $input = $request->input('input');
        $accounts = LedgerAccount::query()->whereRaw('LENGTH(code) >= 6');
        if (empty($input)) {
            return new LedgerAccountCollection($accounts->paginate(20));
        }

        $accounts = $accounts->where('code', 'like', '%' . $input . '%')
            ->orWhere('name', 'like', '%' . $input . '%');

        return new LedgerAccountCollection($accounts->paginate(20));
    }
}

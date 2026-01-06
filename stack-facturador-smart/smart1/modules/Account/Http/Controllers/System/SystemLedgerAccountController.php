<?php

namespace Modules\Account\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Exception;
use App\Http\Resources\System\ClientCollection;
use Hyn\Tenancy\Environment;
use App\Models\System\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\System\Configuration;
use App\Models\System\LedgerAccount;
use App\Models\System\LedgerAccountDescription;
use App\Models\System\LedgerAccountMovement;
use App\Models\System\LedgerAccountRecognition;
use Modules\Account\Http\Controllers\AccountController;
use Modules\Account\Http\Resources\System\LedgerAccountCollection;

class SystemLedgerAccountController extends Controller
{

    public function index()
    {
        return view('account::system.accounting.ledger_account');
    }
    public function detail($detail)
    {
        $code_description = substr($detail, 0, 3);
        $code_movement_recognition = substr($detail, 0, 2);

        $account_description = LedgerAccountDescription::where('code', $code_description)->first();
        $account_recognition = LedgerAccountRecognition::where('code', $code_movement_recognition)->first();
        $account_movement = LedgerAccountMovement::where('code', $code_movement_recognition)->first();

        return response()->json([
            'description' => $account_description->description,
            'content' => $account_movement->content,
            'recognition' => $account_recognition->recognition,
            'comments' => $account_movement->comments,
            'debit_description' => $account_movement->debit_description,
            'credit_description' => $account_movement->credit_description,
        ]);
    }
    public function delete($code)
    {
        $record = LedgerAccount::where('code', $code)->first();
        $record->delete();
        return response()->json(['success' => true, 'message' => 'Cuenta eliminada con éxito']);
    }
    public function edit()
    {
        $code = request('code');
        $record = LedgerAccount::where('code', $code)->first();
        $name = request('name');

        $record->name = $name;
        $record->save();

        return response()->json(['success' => true, 'message' => 'Cuenta actualizada con éxito']);
    }

    public function records(Request $request)
    {
        $records = LedgerAccount::query();
        $records->when($request->code, function ($query, $value) {
            return $query->where('code', 'like', '%' . $value . '%');
        });
        $records->when($request->name, function ($query, $value) {
            return $query->where('name', 'like', '%' . $value . '%');
        });

        return new LedgerAccountCollection($records->paginate(20));
    }
    public function records5Length(Request $request)
    {
        $records = LedgerAccount::query();
        $records->when($request->code, function ($query, $value) {
            return $query->where('code', 'like', '%' . $value . '%');
        });
        $records->when($request->name, function ($query, $value) {
            return $query->where('name', 'like', '%' . $value . '%');
        });
        $records->where(DB::raw('LENGTH(code)'), 5);

        return new LedgerAccountCollection($records->paginate(20));
    }

    public function download(Request $request)
    {

        $client = Client::findOrFail($request->id);
        $tenancy = app(Environment::class);
        $tenancy->tenant($client->hostname->website);

        return app(AccountController::class)->download($request);
    }
}

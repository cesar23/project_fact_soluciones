<?php

namespace App\Http\Controllers\Tenant;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tenant\NameQuotations;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(auth()->user()->type == 'integrator')
            return redirect('/documents');

        $is_contingency = 0;
        $api_token = \App\Models\Tenant\Configuration::getApiServiceToken();
        $data = NameQuotations::first();
        $quotations_optional =  $data!=null ? $data->quotations_optional : null;
        $quotations_optional_value =  $data!=null ? $data->quotations_optional_value : null;
        return view('tenant.documents.form', compact('is_contingency','api_token','quotations_optional','quotations_optional_value'));
    }
}

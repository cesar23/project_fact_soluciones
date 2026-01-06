<?php

namespace Modules\Restaurant\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Tenant\Configuration;
use Illuminate\Routing\Controller;
use Modules\Restaurant\Models\Table;

class AttentionController extends Controller
{


    public function index()
    {
        $configurations = Configuration::first()->getCollectionData();
        $tables = Table::get();
        return view('restaurant::attention',compact('configurations','tables'));
    }
    
    
}

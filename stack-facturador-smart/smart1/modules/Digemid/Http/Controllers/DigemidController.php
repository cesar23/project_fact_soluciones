<?php

namespace Modules\Digemid\Http\Controllers;

use App\Models\Tenant\Configuration;
use App\Models\Tenant\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class DigemidController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('digemid::index');
    }
    public function updateExportableItem(Item $item){

        $catDigemid = $item->cat_digemid()->first();
        if(!empty($catDigemid)) {
            $catDigemid->toggleActive()->push();
        }
        $configuration = Configuration::first();
        return $item->getCollectionData($configuration);
    }


}

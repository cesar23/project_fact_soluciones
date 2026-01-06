<?php

namespace Modules\Seller\Http\Controllers;

use App\Models\Tenant\Document;
use App\Models\Tenant\SaleNote;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Seller\Models\RecordSellerSale;

class SellerController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('seller::index');
    }
    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function monthySalesIndex()
    {
        return view('seller::monthly-sales');
    }
    /**
     * Show the form for creating a new resource.
     * @param Request $request
     * @return Response
     */
    public function records(Request $request)
    {
        $records = $this->getRecords($request);
        return response()->json($records);
    }

    private function getRecords($request)
    {
        $month = $request->month;
        //en month recibo el mes y año en yyyy-mm

        $models = [
            Document::class,
            SaleNote::class,
        ];
        $results = [];
        foreach ($models as $key => $model) {
            $tableName = (new $model)->getTable(); // Obtiene el nombre de la tabla del modelo
            $records = $model::select(
                'date_of_issue',
                'user_id',
                'seller_id',
                'total',
                'sellers.name as seller_name'
            );
            if ($tableName == 'documents') {
                $records = $records->where('state_type_id', '05');
            } else {
                $records = $records->whereIn('state_type_id', ['01', '05']);
            }
            $records = $records->join('users as sellers', "sellers.id", '=', "{$tableName}.seller_id")

                ->where('date_of_issue', 'like', "$month%")
                ->get();
            $results[$key] = $records;
        }
        $combined = null;

        foreach ($results as $result) {
            if ($combined == null) {
                $combined = $result;
            } else {
                $combined = $combined->mergeRecursive($result);
            }
        }

        $summedResults = $combined->groupBy('seller_id')->map(function ($items, $seller_id) {
            $total = collect($items)->sum('total');
            $total_documents = collect($items)->count();
            $max_sale = RecordSellerSale::where('user_id', $seller_id)->max('total');

            return [
                'seller_name' => $items[0]->seller_name, // 'seller_name' => 'seller_name
                'seller_id' => $seller_id,
                'max_sale' => $max_sale,
                'total_sales' => $total,
                'total_documents' => $total_documents
            ];
        });

        return [
            'records' => $summedResults,
            'total_sales' => $summedResults->sum('total_sales'),
            'total_documents' => $summedResults->sum('total_documents')
        ];
    }


    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('seller::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('seller::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('seller::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}

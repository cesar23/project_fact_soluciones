<?php

namespace Modules\Account\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Document;
use App\Models\Tenant\Purchase;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaxReturnController extends Controller
{
    protected $date;
    public function index()
    {


        return view('account::tax_return.index');
    }

    function get_sum($model, $column, $igv = 18)
    {
        $state_type_id = $model == Purchase::class ? '01' : '05';
        $carbon = $this->date;
        $total = $model::select(
            DB::raw('SUM(CASE WHEN document_type_id = "07" THEN -' . $column . ' ELSE ' . $column . ' END) as ' . $column . '_sum')
        )
            ->whereYear('date_of_issue', $carbon->year)->where('state_type_id', $state_type_id);
        if ($model == Purchase::class) {


            $total = $total->whereHas('items', function ($query) use ($igv) {
                $query->where('percentage_igv', $igv);
            });
        }

            if($model == Document::class){
                $total = $total->whereMonth('date_of_issue', $carbon->month);
            }else{
                $total = $total->where(
                    function ($q) use ($carbon) {
                        $q->whereNull('sunat_date')
                            ->whereMonth('date_of_issue', $carbon->month);
                        $q->orWhereNotNull('sunat_date')
                            ->whereMonth('sunat_date',  $carbon->month);
                    }
                );
            }

           $total = $total->where('state_type_id', $state_type_id)
            ->first()
            ->{$column . '_sum'};

        return $total;
    }

    public function records(Request $request)
    {

        $date = $request->date;
        $carbon = Carbon::createFromFormat('m-Y', $date);
        if ($carbon == false) {
            return response()->json(["message" => "Fecha inválida"], 400);
        }
        $this->date = $carbon;
        $sale_total = (float)$this->get_sum(Document::class, "total_value");
        $purchase_total = (float) $this->get_sum(Purchase::class, "total_value");
        $purchase_total_10 = (float) $this->get_sum(Purchase::class, "total_value", 10);


        return compact('sale_total', 'purchase_total', 'purchase_total_10');
    }
}

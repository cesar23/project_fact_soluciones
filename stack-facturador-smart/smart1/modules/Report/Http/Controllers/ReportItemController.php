<?php

namespace Modules\Report\Http\Controllers;

use App\Models\Tenant\Catalogs\DocumentType;
use App\Http\Controllers\Controller;
use App\Models\Tenant\PurchaseItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Report\Exports\ItemExport;
use Illuminate\Http\Request;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Document;
use App\Models\Tenant\DocumentItem;
use App\Models\Tenant\Company;
use App\Models\Tenant\QuotationItem;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\SaleNoteItem;
use Carbon\Carbon;
use Modules\Report\Http\Resources\ItemCollection;
use Modules\Report\Traits\ReportTrait;


class ReportItemController extends Controller
{
    use ReportTrait;

    public function filter()
    {

        $document_types = [];
        $items = $this->getItems('items');
        $establishments = [];
        $web_platforms = $this->getWebPlatforms();

        return compact('document_types', 'establishments', 'items', 'web_platforms');
    }


    public function index()
    {

        return view('report::items.index');
    }

    public function records(Request $request)
    {
        $itemsPerPage = config('tenant.items_per_page');
        $type = isset($request['type']) ? $request['type'] : null;
        
        if (!is_integrate_system()||$type == 'purchase') {
            $records = $this->getRecordsItems($request->all(), DocumentItem::class);
            return new ItemCollection($records->paginate($itemsPerPage));
        } else {
            $models = [DocumentItem::class, SaleNoteItem::class, QuotationItem::class];
            $all_records = collect();
    
            foreach ($models as $model) {
                $records = $this->getRecordsItems($request->all(), $model);
                $all_records = $all_records->merge($records->get());
            }
    
            // Usar LengthAwarePaginator para paginar la colección
            $page = request()->input('page', 1);
            $paginatedRecords = new \Illuminate\Pagination\LengthAwarePaginator(
                $all_records->forPage($page, $itemsPerPage),
                $all_records->count(),
                $itemsPerPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );
    
            return new ItemCollection($paginatedRecords);
        }
    }


    public function getRecordsItems($request, $model)
    {

        // dd($request['period']);
        $document_type_id = isset($request['document_type_id']) ? $request['document_type_id'] : null;
        $establishment_id = isset($request['establishment_id']) ? $request['establishment_id'] : null;
        $period = isset($request['period']) ? $request['period'] : null;
        $date_start = isset($request['date_start']) ? $request['date_start'] : null;
        $date_end = isset($request['date_end']) ? $request['date_end'] : null;
        $month_start = isset($request['month_start']) ? $request['month_start'] : null;
        $month_end = isset($request['month_end']) ? $request['month_end'] : null;
        $item_id = isset($request['item_id']) ? $request['item_id'] : null;
        $web_platform_id = isset($request['web_platform_id']) ? $request['web_platform_id'] : null;
        $type = isset($request['type']) ? $request['type'] : null;
        if ($type == 'purchase') {
            $model = PurchaseItem::class;
        }

        $d_start = null;
        $d_end = null;

        /** @todo: Eliminar periodo, fechas y cambiar por

        $date_start = $request['date_start'];
        $date_end = $request['date_end'];
        \App\CoreFacturalo\Helpers\Functions\FunctionsHelper\FunctionsHelper::setDateInPeriod($request, $date_start, $date_end);
         */

        switch ($period) {
            case 'month':
                $d_start = Carbon::parse($month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($month_start . '-01')->endOfMonth()->format('Y-m-d');
                // $d_end = Carbon::parse($month_end.'-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'between_months':
                $d_start = Carbon::parse($month_start . '-01')->format('Y-m-d');
                $d_end = Carbon::parse($month_end . '-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'date':
                $d_start = $date_start;
                $d_end = $date_start;
                // $d_end = $date_end;
                break;
            case 'between_dates':
                $d_start = $date_start;
                $d_end = $date_end;
                break;
        }

        $records = $this->dataItems($document_type_id, $establishment_id, $d_start, $d_end, $item_id, $model, $web_platform_id, $type);

        return $records;
    }


    /**
     * @param      $document_type_id
     * @param      $establishment_id
     * @param      $date_start
     * @param      $date_end
     * @param      $item_id
     * @param      $model
     * @param      $web_platform_id
     * @param null $type
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function dataItems(
        $document_type_id,
        $establishment_id,
        $date_start,
        $date_end,
        $item_id,
        $model,
        $web_platform_id,
        $type = null
    ) {
        $data = $model::where('item_id', $item_id);
        $model_literal = $model === SaleNoteItem::class ? 'sale_note' : ($model == QuotationItem::class ? 'quotation' : 'document');
        if ($model !== PurchaseItem::class) {
            $data->whereHas($model_literal, function ($query) use ($date_start, $date_end,$model_literal) {
                $query
                    ->whereBetween('date_of_issue', [$date_start, $date_end]);
                    if($model_literal == 'document'){
                        $query->whereIn('document_type_id', ['01', '03']);
                    }
                    $query->whereIn('state_type_id', ['01', '03', '05', '07', '13'])
                    ->latest()
                    ->whereTypeUser();
            });
            if ($web_platform_id) {

                $data = $data->whereHas('relation_item', function ($q) use ($web_platform_id) {
                    $q->where('web_platform_id', $web_platform_id);
                });
            }
        } else {
            $data->whereHas('purchase', function ($query) use ($date_start, $date_end) {
                /** @var \Illuminate\Database\Eloquent\Builder $query */
                $query
                    ->whereBetween('date_of_due', [$date_start, $date_end])
                    //->whereIn('document_type_id', ['01','03'])
                    ->WhereStateTypeAccepted()
                    ->latest()
                    ->whereTypeUser();
            });
        }

        return $data;
    }



    public function excel(Request $request)
    {

        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;
        if ($request->has('type') && $request->type === 'purchase') {
            $model = PurchaseItem::class;
        } else {
            $model = DocumentItem::class;
        }
        $models = [DocumentItem::class, SaleNoteItem::class, QuotationItem::class];
        $all_records = collect();
        if(!is_integrate_system() || ($request->has('type') && $request->type === 'purchase')){
            $records = $this->getRecordsItems($request->all(), $model)->get();
        }else{
            foreach ($models as $model) {
                $records = $this->getRecordsItems($request->all(), $model);
                $all_records = $all_records->merge($records->get());
            }
            $records = $all_records;
        }
        // $records = collect($this->getRecordsItems($request->all(), $model)->get());
        $itemExport = new ItemExport();
        $fileName = 'Reporte_Ventas_por_Producto_' . Carbon::now() . '.xlsx';
        if ($request->has('type') && $request->type === 'purchase') {
            $itemExport->setType($request->type);
            $fileName = 'Reporte_Compras_por_Producto_' . Carbon::now() . '.xlsx';
        }

        return $itemExport
            ->records($records)
            ->company($company)
            ->establishment($establishment)
            ->download($fileName);
    }
}

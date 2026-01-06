<?php

namespace App\Services;

use App\CoreFacturalo\Services\Ruc\Sunat;
use App\Models\Tenant\Company;
use App\Models\Tenant\Document;
use App\Models\Tenant\DocumentItem;
use App\Models\Tenant\InventoryKardex;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\PurchaseItem;
use App\Models\Tenant\SunatStock;

class SunatStockService
{
    protected $item_id;
    protected $establishment_id;
    protected $init_date;
    protected $dates;
    protected $date_start;
    protected $date_end;
    protected $soap_type;
    protected $date_stock;

    public function __construct($item_id, $establishment_id = null,$date_stock)
    {
        $this->soap_type = Company::active()->soap_type_id;
        $this->item_id = $item_id;
        $this->establishment_id = $establishment_id;
        $date_stock = date('Y-m-01', strtotime($date_stock));
        $this->date_stock = $date_stock;

        
    }
    public function get_sunat_stock(){
        $sunat_stock = SunatStock::where('item_id', $this->item_id)->where('period', $this->date_stock)->first();
        $start_stock = 0;
        if($sunat_stock){
            $start_stock = $sunat_stock->start_stock;
        }
        return $start_stock;

    }
    private function dates_to_today()
    {
        $init_date = $this->init_date;
        $init_date = date('Y-m-01', strtotime($init_date));
        $today = date('Y-m-d');
        $dates = [];
        $dates[] = $init_date;
        while ($init_date < $today) {
            $init_date = date('Y-m-d', strtotime($init_date . ' +1 month'));
            $dates[] = $init_date;
        }
        $this->dates = $dates;
    }
    private function get_first_month()
    {
        $purchase_item = PurchaseItem::where('item_id', $this->item_id)->first();
        $date_of_issue = null;
        if($purchase_item!= null){
            $purchase = $purchase_item->purchase;
            if ($purchase->sunat_date) {
                $date_of_issue = $purchase->sunat_date;
            } else {
                $date_of_issue = $purchase->date_of_issue;
            }
        }else{
            $document_item = DocumentItem::where('item_id', $this->item_id)->first();
            if($document_item!= null){
                $document = $document_item->document;
                if ($document->sunat_date) {
                    $date_of_issue = $document->sunat_date;
                } else {
                    $date_of_issue = $document->date_of_issue;
                }
            }
        }
      
        $this->init_date = $date_of_issue;
    }
    public function sunat_stock(){
        $sunat_stock = SunatStock::where('item_id', $this->item_id)->orderBy('id', 'desc')->first();
        if($sunat_stock){
            $this->init_date = $sunat_stock->period;
            $sunat_stock->delete();
        }else{
            $this->get_from_the_beginning();
        }
        $this->dates_to_today();
        $this->create_sunat_stock();
    }
     function get_from_the_beginning()
    {
        $this->get_first_month();
      
       
    }
    public function get_sunat_stock_between_dates($date_start, $date_end)
    {
        $this->date_start = $date_start;
        $this->date_end = $date_end;
        $purchases = $this->purchases();
        $documents = $this->documents();
        $notes = $this->notes();
        $total = $purchases - $documents - $notes;
        return $total;

    }
    function create_sunat_stock(){
        $dates = $this->dates;
        foreach ($dates as $date) {
            $date_past = date('Y-m-d', strtotime($date . ' -1 month'));
            $sunat_stock_past = SunatStock::where('item_id', $this->item_id)->where('period', $date_past)->first();
            $end_stock = 0;
            if ($sunat_stock_past) {
                $end_stock = $sunat_stock_past->end_stock;
            }
            $this->date_start_end($date);
            $purchases = $this->purchases();
            $documents = $this->documents();
            $notes = $this->notes();
            $total = $purchases - $documents - $notes;
            SunatStock::create([
                'item_id' => $this->item_id,
                'period' => $date,
                'start_stock' => $end_stock,
                'end_stock' => $total + $end_stock,
                'warehouse_id' => $this->establishment_id
            ]);
        }
    }
    function  date_start_end($date)
    {
        $date_start = $date;
        $date_end = date('Y-m-t', strtotime($date_start));
        $this->date_start = $date_start;
        $this->date_end = $date_end;
    }
    function purchases()
    {
        $purchases = $this->sum_quantity(PurchaseItem::class);
        $total = $purchases->sum('quantity');
        return $total;
    }
    function documents()

    {
        $documents = $this->sum_quantity(DocumentItem::class);
        $total = $documents->sum('quantity');
        return $total;
    }

    function notes(){
            
            $notes = $this->sum_quantity(DocumentItem::class,['07']);
            $total = $notes->sum('quantity');
            return $total;
    }

    function sum_quantity($model, $types = ['01','03','08'])
    {
        $state_type_id = $model == PurchaseItem::class ? '01' : '05';
        $relation = $model == PurchaseItem::class ? 'purchase' : 'document';
        $query = $model::whereHas($relation, function ($query) use ($state_type_id, $model,$types) {
            $query->where('state_type_id', $state_type_id)->where('soap_type_id', $this->soap_type);
            if($model == DocumentItem::class){
                $query->whereIn('document_type_id',$types);
            }
            if ($model == PurchaseItem::class) {
                $query->whereBetween('sunat_date', [$this->date_start, $this->date_end])
                    ->orWhere(function ($query) {
                        $query->whereNull('sunat_date')
                            ->whereBetween('date_of_issue', [$this->date_start, $this->date_end]);
                    });
            } else {
                $query->whereBetween('date_of_issue', [$this->date_start, $this->date_end])
                
                ;
            }
        })->where('item_id', $this->item_id);
        return $query;
    }
}

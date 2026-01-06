<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Catalogs\CurrencyType;
use Modules\Finance\Models\GlobalPayment;
use Modules\Pos\Models\CashTransaction;
use Modules\Sale\Models\QuotationPayment;

/**
 * App\Models\Tenant\Cash
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tenant\CashDocument[] $cash_documents
 * @property-read int|null $cash_documents_count
 * @property-read CashTransaction|null $cash_transaction
 * @property-read mixed $number_full
 * @property-read \Illuminate\Database\Eloquent\Collection|GlobalPayment[] $global_destination
 * @property-read int|null $global_destination_count
 * @property-read \Illuminate\Database\Eloquent\Collection|GlobalPayment[] $global_payments
 * @property-read int|null $global_payments_count
 * @property-read \App\Models\Tenant\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Cash newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cash newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cash query()
 * @method static \Illuminate\Database\Eloquent\Builder|Cash whereTypeUser()
 * @mixin \Eloquent
 */
class Cash extends ModelTenant
{
    // protected $with = ['cash_documents'];

    protected $table = 'cash';

    protected $fillable = [
        'website_id',
        'company',
        'counter',
        'alter_company',
        'user_id',
        'date_opening',
        'time_opening',
        'date_closed',
        'time_closed',
        'beginning_balance',
        'final_balance',
        'final_balance_with_banks',
        'income',
        'state',
        'reference_number',
        'apply_restaurant',
        'establishment_id',
        'currency_type_id',
        'final_balance_to_next_cash',
        'exchange_rate_sale',
    ];

    protected $casts = [
        'counter' => 'array',
    ];


    public static function getCashByUserId($user_id)
    {
        return self::where('user_id', $user_id)->where('state', true)->first();
    }

    public function getCounterAttribute($value)
    {
        return (is_null($value)) ? null : json_decode($value);
    }

    public function setCounterAttribute($value)
    {
        $this->attributes['counter'] = (is_null($value)) ? null : json_encode($value);
    }

    public function setAlterCompanyAttribute($value)
    {
        $this->attributes['alter_company'] = (is_null($value)) ? null : json_encode($value);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function establishment()
    {
        return $this->belongsTo(Establishment::class);
    }   

    public function currency_type()
    {
        return $this->belongsTo(CurrencyType::class, 'currency_type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cash_documents()
    {
        return $this->hasMany(CashDocument::class);
    }

    /**
     * @param $query
     *
     * @return null
     */
    public function scopeWhereTypeUser($query)
    {
        /** @var \App\Models\Tenant\User $user */
        $user = auth()->user();
        if (!$user) {
            return null;
        }
        return ($user->type === 'seller') ? $query->where('user_id', $user->id) : null;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function global_destination()
    {
        return $this->morphMany(GlobalPayment::class, 'destination');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function global_payments()
    {
        return $this->morphToMany(GlobalPayment::class, 'destination');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cash_transaction()
    {
        return $this->hasOne(CashTransaction::class);
    }



    /**
     * @return string
     */
    public function getNumberFullAttribute()
    {

        if($this->cash_transaction){
            return "{$this->cash_transaction->description} - Caja chica POS".($this->reference_number ? ' N° '.$this->reference_number:'');
        }

        return '-';

    }

    public function scopeWhereActive($query)
    { 
        return $query->where([
            ['user_id', auth()->user()->id],
            ['state', true],
        ]);
    }

    public function cash_documents_credit()
    {
        return $this->hasMany(CashDocumentCredit::class);
    }
    

    /**
     * 
     * Obtener total de ingresos por tipo de documento
     *
     * @return array
     */
    public function getTotalsIncomeSummary()
    {

        $state_types_accepted = ['01', '03', '05', '07', '13'];

        // $document_total_payments = $this->cash_documents()
        //                     ->whereHas('document')
        //                     ->get()
        //                     ->sum(function($row){
        //                         return $row->document->getTotalAllPayments();
        //                     });
        
        
        // $sale_note_total_payments = $this->cash_documents()
        //                     ->whereHas('sale_note')
        //                     ->get()
        //                     ->sum(function($row){
        //                         return $row->sale_note->getTotalAllPayments();
        //                     });
        $cash = $this;
        $document_total_payments = 0;
        $sale_note_total_payments = 0;
        $quotation_total_payments = 0;
        $documents_payments = DocumentPayment::whereHas('document', function ($query) use ($cash, $state_types_accepted) {
            $query->where('cash_id', $cash->id)
                ->whereIn('state_type_id', $state_types_accepted);
        })->get();


        foreach ($documents_payments as $document_payment) {
            $document_total_payments += $document_payment->payment;

        }


        $sale_note_payments = SaleNotePayment::whereHas('sale_note', function ($query) use ($cash, $state_types_accepted) {
            $query->where('cash_id', $cash->id)
                ->whereIn('state_type_id', $state_types_accepted)
                ->where(function ($query) {
                    $query->whereNull('quotation_id') // No tiene cotización
                        ->orWhereHas('quotation', function ($q2) {
                            $q2->doesntHave('payments'); // Tiene cotización, pero sin pagos
                        });
                });
        })->get();


        foreach ($sale_note_payments as $payment) {
            $sale_note_total_payments += $payment->payment;
        }


        $quotation_payments = QuotationPayment::whereHas('quotation', function ($query) use ($cash, $state_types_accepted) {
            $query->where('cash_id', $cash->id)
                ->whereIn('state_type_id', $state_types_accepted);
        })
            ->whereDoesntHave('global_payment')
            ->get();

        $quotation_payments_global_payment = QuotationPayment::whereHas('global_payment', function ($query) use ($cash, $state_types_accepted) {
            $query->where('destination_id', $cash->id)
                ->where('destination_type', Cash::class);
        })->get();

        $quotation_payments = $quotation_payments->merge($quotation_payments_global_payment);

        foreach ($quotation_payments as $payment) {
            $quotation_total_payments += $payment->payment;
        }

        return [
            'document_total_payments' => $this->generalApplyNumberFormat($document_total_payments),
            'sale_note_total_payments' => $this->generalApplyNumberFormat($sale_note_total_payments),
            'quotation_total_payments' => $this->generalApplyNumberFormat($quotation_total_payments),
        ];
        
    }
    
    
    /**
     * 
     * Obtener comprobantes y notas de venta ordenados para reporte ingresos en caja
     *
     * @return array
     */
    public function getIncomePaymentsData()
    {
        
        // $documents = $this->cash_documents()
        //                 ->join('documents', 'documents.id', '=', 'cash_documents.document_id')
        //                 ->orderBy('documents.document_type_id')
        //                 ->orderBy('documents.created_at')
        //                 ->get();
        
        // $sale_notes = $this->cash_documents()
        //                     ->join('sale_notes', 'sale_notes.id', '=', 'cash_documents.sale_note_id')
        //                     ->orderBy('sale_notes.created_at')
        //                     ->get();
        $state_types_accepted = ['01', '03', '05', '07', '13'];
        $documents = DocumentPayment::whereHas('document', function ($query) use ($state_types_accepted) {
            $query->where('cash_id', $this->id)
                ->whereIn('state_type_id', $state_types_accepted);
        })->get();

        
        $sale_notes = SaleNotePayment::whereHas('sale_note', function ($query) use ($state_types_accepted) {
            $query->where('cash_id', $this->id)
                ->whereIn('state_type_id', $state_types_accepted)
                ->where(function ($query) {
                    $query->whereNull('quotation_id') // No tiene cotización
                        ->orWhereHas('quotation', function ($q2) {
                            $q2->doesntHave('payments'); // Tiene cotización, pero sin pagos
                        });
                });
        })->get();


        $quotation_payments = QuotationPayment::whereHas('quotation', function ($query) use ($state_types_accepted) {
            $query->where('cash_id', $this->id)
                ->whereIn('state_type_id', $state_types_accepted);
        })
            ->whereDoesntHave('global_payment')
            ->get();

        $quotation_payments_global_payment = QuotationPayment::whereHas('global_payment', function ($query) use ($state_types_accepted) {
            $query->where('destination_id', $this->id)
                ->where('destination_type', Cash::class);
        })->get();

        $quotations = $quotation_payments->merge($quotation_payments_global_payment);


        return [
            'documents' => $documents,
            'sale_notes' => $sale_notes,
            'quotations' => $quotations,
        ];
        
    }


    /**
     * 
     * Filtrar cajas del usuario que realiza la petición
     * 
     * Usado en:
     * caja - app
     *
     * @param  Builder $query
     * @param  string $input
     * @return Builder
     */
    public function scopeWhereFilterRecordsApi($query, $input)
    {

        return $query->where(function($q) use($input){
                        $q->where('income', 'like', "%{$input}%" )
                            ->orWhere('reference_number','like', "%{$input}%");
                    })
                    ->where('user_id', auth()->id())
                    ->latest();
    }

        
    /**
     * 
     * Obtener datos para api (app)
     *
     * @return array
     */
    public function getApiRowResource()
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->user->name,
            'date_opening' => $this->date_opening,
            'time_opening' => $this->time_opening,
            'counter' => $this->counter,
            'opening' => "{$this->date_opening} {$this->time_opening}",
            'date_closed' => $this->date_closed,
            'time_closed' => $this->time_closed, 
            'closed' => !$this->state ? "{$this->date_closed} {$this->time_closed}" : null,
            'beginning_balance' => (float) $this->beginning_balance,
            'final_balance' => (float) $this->final_balance,
            'income' => (float) $this->income,
            'state' => (bool) $this->state, 
            'state_description' => $this->state_description,
            'reference_number' => $this->reference_number,
        ];
    }

    
    /**
     * 
     * @return string
     */
    public function getStateDescriptionAttribute()
    {
        return ($this->state) ? 'Aperturada':'Cerrada';
    }

        
    /**
     * 
     * Se agrega scope polimorfico para filtrar destino en global payment
     *
     * @param  Builder $query
     * @return Builder
     */
    public function scopeWithBankIfExist($query)
    {
        return $query;
    }

    
    /**
     * 
     * Obtener relaciones necesarias o aplicar filtros para reporte pagos - finanzas
     *
     * @param  Builder $query
     * @return Builder
     */
    public function scopeFilterRelationsGlobalPayment($query)
    {
        return $query->with([
                        'cash_transaction'
                    ]);
    }

    
    /**
     * 
     * Filtro para reporte general de caja v2
     *
     * @param  Builder $query
     * @return Builder
     */
    public function scopeFilterDataGeneralCashReport($query)
    {
        return $query->with([
            'global_destination' => function($query){
                return $query->generalCashReportWithPayments()->latest();
            }
        ]);
    }

}

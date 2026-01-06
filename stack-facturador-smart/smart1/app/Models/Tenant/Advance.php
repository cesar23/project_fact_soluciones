<?php

namespace App\Models\Tenant;

use Modules\Finance\Models\GlobalPayment;
use Modules\Pos\Models\CashTransaction;

/**
 * App\Models\Tenant\Cash
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tenant\CashDocument[] $cash_documents
 * @property-read int|null $cash_documents_count
 * @property-read CashTransaction|null $cash_transaction
 * @property-read mixed $currency_type_id
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
class Advance extends ModelTenant
{
    // protected $with = ['cash_documents'];

    protected $table = 'advances';

    protected $fillable = [
        'person_id',
        'date_opening',
        'time_opening',
        'date_closed',
        'time_closed',
        'beginning_balance',
        'final_balance',
        'state',
        'reference_number',
    ];
    public function scopeWhereTypePerson($query, $type)
    {
        return $query->whereHas('person', function ($query) use ($type) {
            $query->where('type', $type);
        });
    }

    public function discount($amount)
    {
        $this->final_balance = $this->final_balance - $amount;
        $this->save();
    }

    // public function setAlterCompanyAttribute($value)
    // {
    //     $this->attributes['alter_company'] = (is_null($value)) ? null : json_encode($value);
    // }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function advance_documents()
    {
        return $this->hasMany(AdvanceDocument::class);
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
     * @return string
     */
    public function getCurrencyTypeIdAttribute()
    {
        return 'PEN';
    }
}

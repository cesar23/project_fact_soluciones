<?php


namespace Modules\Order\Models;

use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\ModelTenant;

/**
 * Class DocumentFee
 *
 * @package App\Models\Tenant
 */
class OrderNoteFee extends ModelTenant
{
    public $timestamps = false;
    protected $table = 'order_note_fee';

    protected $fillable = [
        'order_note_id',
        'date',
        'currency_type_id',
        'amount',
    ];

    protected $casts = [
        // 'date' => 'date',
        'amount' => 'float',
    ];

    


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order_note()
    {
        return $this->belongsTo(OrderNote::class, 'order_note_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency_type()
    {
        return $this->belongsTo(CurrencyType::class, 'currency_type_id');
    }

}

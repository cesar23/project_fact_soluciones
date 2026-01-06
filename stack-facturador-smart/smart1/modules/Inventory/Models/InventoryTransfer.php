<?php

namespace Modules\Inventory\Models;

use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Dispatch;
use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\User;
use Carbon\Carbon;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class InventoriesTransfer
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $description
 * @property int $warehouse_id
 * @property int $warehouse_destination_id
 * @property float $quantity
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Warehouse $warehouse_destination
 * @property User $user
 * @property Warehouse $warehouse
 * @property Collection|Inventory[] $inventories
 *
 * @package Modules\Inventory\Models
 * @mixin ModelTenant
 * @property-read int|null $inventories_count
 * @property-read Collection|\Modules\Inventory\Models\Inventory[] $inventory
 * @property-read int|null $inventory_count
 * @method static Builder|InventoryTransfer newModelQuery()
 * @method static Builder|InventoryTransfer newQuery()
 * @method static Builder|InventoryTransfer query()
 */
class InventoryTransfer extends ModelTenant
{


    protected $table = 'inventories_transfer';

    use UsesTenantConnection;

    protected $fillable = [

        'state',
        'external_id',
        'user_id',
        'soap_type_id',
        'document_type_id',
        'series',
        'number',
        'description',
        'warehouse_id',
        'warehouse_destination_id',
        'quantity',
        'user_accept_id',
        'dispatch_id',
        'purchase_id',
        'filename'
    ];
    protected $casts = [
        'warehouse_id' => 'int',
        'warehouse_destination_id' => 'int',
        'user_id' => 'int',
        'quantity' => 'float'
    ];

    //        /**
    //         * The "booting" method of the model.
    //         *
    //         * @return void
    //         */
    //        protected static function boot()
    //        {
    //            parent::boot();
    //            static::creating(function (self $model) {
    //                $model->user_id = 0;
    //                if (auth() and auth()->user() and auth()->user()->id) {
    //                    $model->user_id = auth()->user()->id;
    //                }
    //
    //            });
    //        }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'inventories_transfer_id');
    }

    public function items()
    {
        return $this->hasMany(InventoryTransferItem::class);
    }
    public function user_to_accept()
    {
        return $this->belongsTo(User::class, 'user_accept_id');
    }
    public function dispatch()
    {
        return $this->belongsTo(Dispatch::class, 'dispatch_id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function warehouse_destination()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_destination_id');
    }

    public function getNumberFullAttribute()
    {
        return $this->series . ' ' . $this->number;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inventory()
    {
        return $this->hasMany(Inventory::class, 'inventories_transfer_id');
    }
    public function inventory_to_accept()
    {
        return $this->hasMany(InventoryTransferToAccept::class, 'inventory_transfer_id');
    }
    public function inventory_transfer_item()
    {
        return $this->hasMany(InventoryTransferItem::class);
    }

    public function getCollectionData()
    {
    
        $user = auth()->user();
        $warehouse = Warehouse::where('establishment_id', $user->establishment_id)->first();
        $warehouse_id = $warehouse->id;
        $can_confirm = $warehouse_id === $this->warehouse_destination_id;
        $can_confirm = $this->user_accept_id ? ($this->user_accept_id === $user->id) : $can_confirm;
        $user_to_accept = optional($this->user_to_accept)->name;
        $description = $user_to_accept ? "{$this->description} - Para ser aceptado por {$user_to_accept}" : $this->description;
        return [
            'can_confirm' => $can_confirm,
            'state' => $this->state,
            'series' => $this->series,
            'number' => $this->number,
            'id' => $this->id,
            'user_name' => $this->user->name,
            'description' => $description,
            'quantity' => round($this->quantity, 1),
            'warehouse' => $this->warehouse->description,
            'warehouse_destination' => $this->warehouse_destination->description,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),

            
        
        ];
    }

    public function getPdfData()
    {

        $data = [];
        $data['serie'] = $this->series;
        $data['number'] = $this->number;
        $data['document_type'] = "NOTA DE TRASLADO";
        $data['motivo'] = $this->description;
        $data['created_at'] = $this->created_at;
        $data['quantity'] = $this->quantity;
        $data['warehouse_from'] = $this->warehouse;
        $data['warehouse_to'] = $this->warehouse_destination;
        $data['user'] = $this->user;
        $data['inventories'] = $this->inventories;
        $data['item_transfers'] = $this->inventory_transfer_item->transform(function ($o) {
            if ($o->item_lots_group_id != null) {
                return [
                    'item_id' => $o->item_lots_group->item_id,
                    'code' => $o->item_lots_group->code,
                ];
            }
            if ($o->item_lot_id != null) {
                return [
                    'item_id' => $o->item_lot->item_id,
                    'code' => $o->item_lot->series,
                ];
            }
        });;
        $data['configuration'] = Configuration::first();
        $data['company'] = Company::active();

        return $data;
    }
}

<?php


namespace App\Models\Tenant;


use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Zone
 *
 * @property int    $id
 * @property string|null $name
 * @package  App\Models\Tenant
 */
class Zone extends ModelTenant
{

    public $timestamps = false;
    protected $perPage = 25;
    protected $fillable = [
        'name',
        'code',
        'sector_id'
    ];

    // Agregar debug para ver si se está consultando
    public static function boot()
    {
        parent::boot();

        static::retrieved(function ($zone) {
        });
    }
    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     *
     * @return Zone
     */
    public function setName(?string $name): Zone
    {
        $this->name = $name;
        return $this;
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }
}

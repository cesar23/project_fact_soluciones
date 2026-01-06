<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;

class Personal extends Model implements AuthenticatableContract
{
    use Authenticatable;

    protected $table = 'personal';
    protected $connection = 'tenant';

    protected $fillable = [
        'id','nombre', 'idrol', 'genero', 'usuario', 'contraseña',
    ];

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'idrol');
    }
}



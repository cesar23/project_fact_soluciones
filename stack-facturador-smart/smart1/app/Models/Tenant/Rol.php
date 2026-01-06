<?php
namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'roles';
    protected $fillable = [
        'nombre',
    ];

    protected $connection = 'tenant';
}

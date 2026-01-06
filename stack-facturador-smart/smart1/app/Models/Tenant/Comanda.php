<?php
namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Comanda extends Model
{
    protected $table = 'comandas';
    protected $fillable = [
        'nombre',
        'codigo',

    ];

    protected $connection = 'tenant';
}
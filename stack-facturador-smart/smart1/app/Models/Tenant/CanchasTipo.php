<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CanchasTipo extends Model
{
    use HasFactory;

    protected $table = 'canchas_tipo';

    protected $fillable = [
        'nombre',
        'ubicacion',
        'capacidad',
        'description'
    ];
    
    protected $connection = 'tenant'; // Asegúrate de usar la conexión 'tenant'
}

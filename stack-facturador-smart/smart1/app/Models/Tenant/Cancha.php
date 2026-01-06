<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Cancha extends Model
{
    protected $table = 'canchas';
    protected $fillable = [
        'description',
        'customer_id',
        'type_id',
        'nombre',
        'ubicacion',
        'numero',
        'capacidad',
        'reservante_nombre',
        'reservante_apellidos',
        'hora_reserva',
        'fecha_reserva',
        'tiempo_reserva',
        'ticket',
    ];

    protected $connection = 'tenant'; // Asegúrate de usar la conexión 'tenant'

    public function canchasTipo()
    {
        return $this->belongsTo(CanchasTipo::class, 'nombre', 'nombre');
    }

    public function customer()
    {
        return $this->belongsTo(Person::class);
    }

    public function type()
    {
        return $this->belongsTo(CanchasTipo::class, 'type_id');
    }
}

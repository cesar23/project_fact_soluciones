<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $table = 'coupons';

    protected $fillable = [
        'nombre', 
        'titulo', 
        'descripcion',
        'imagen',
        'descuento',
        'fecha_caducidad',
        'barcode'];

          protected $connection = 'tenant'; // Asegúrate de usar la conexión 'tenant'
}

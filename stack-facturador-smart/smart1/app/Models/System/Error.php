<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use Hyn\Tenancy\Traits\UsesSystemConnection;

class Error extends Model
{
    use UsesSystemConnection;

    protected $table = 'errors'; // Nombre de la tabla en la base de datos

    protected $fillable = [
        'img', // Campo para la URL de la imagen
        'titulo', // Campo para el título
        'comentario2', // Campo para el comentario
        'adm', // Campo para el mensaje de administrador
        'msg_to_pay',
    ];

    public static function getErrors()
    {
        return self::all();
    }

    // Aquí puedes definir cualquier relación con otros modelos si es necesario
}

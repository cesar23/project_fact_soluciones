<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class EnvioClass extends Model
{
    protected $table = 'envios';

    protected $fillable = [
        'name',
        'descripcion',
    ];
}

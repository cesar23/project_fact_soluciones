<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Desarrollador extends Model
{
    protected $table="desarrollador";
    protected $fillable = [
        'name'
    ];

}

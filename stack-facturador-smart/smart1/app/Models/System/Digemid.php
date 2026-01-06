<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class Digemid extends Model
{
    public $timestamps = false;
    protected $table = "digemid";
    protected $fillable = ['cod_prod', 'nom_prod', 'concent', 'nom_form_farm', 'nom_form_farm_simplif', 'presentac', 'fracciones', 'fec_vcto_reg_sanitario', 'num_regsan', 'nom_titular', 'situacion'];
}

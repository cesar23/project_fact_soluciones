<?php

namespace App\Models\System;
use Hyn\Tenancy\Traits\UsesSystemConnection;

use Illuminate\Database\Eloquent\Model;

class PlanModule extends Model
{
    use UsesSystemConnection;

    public $timestamps = false;
    protected $table = 'module_plans';
    protected $fillable = [
        'plan_id',
        'module_id',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    
}

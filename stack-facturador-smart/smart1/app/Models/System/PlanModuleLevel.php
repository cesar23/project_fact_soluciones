<?php

namespace App\Models\System;
use Hyn\Tenancy\Traits\UsesSystemConnection;

use Illuminate\Database\Eloquent\Model;

class PlanModuleLevel extends Model
{
    use UsesSystemConnection;
    
    public $timestamps = false;
    protected $table = 'module_level_plans';
    protected $fillable = [
        'plan_id',
        'module_level_id',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function moduleLevel()
    {
        return $this->belongsTo(Module::class);
    }

    
}

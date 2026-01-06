<?php

namespace App\Models\System;
use Hyn\Tenancy\Traits\UsesSystemConnection;
use Illuminate\Database\Eloquent\Model;

class PermissionToSecondaryAdmin extends Model
{
    use UsesSystemConnection;

    public $timestamps = false;
    protected $table = 'permission_to_secondary_admin';

    protected $fillable = [
        'user_id',
        'permission',
        'value',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
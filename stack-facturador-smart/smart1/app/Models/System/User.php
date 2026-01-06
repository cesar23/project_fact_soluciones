<?php

namespace App\Models\System;

use Hyn\Tenancy\Traits\UsesSystemConnection;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, UsesSystemConnection;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'columns', 'is_secondary'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    protected $casts = [
        'columns' => 'array',
        'is_secondary' => 'boolean',
    ];

    /**
     * 
     * Retorna nombre de la conexión
     *
     * @return string
     */
    public function getDbConnectionName()
    {
        return $this->getConnection()->getName();
    }
    public function permissions()
    {
        return $this->hasMany(PermissionToSecondaryAdmin::class);
    }
}

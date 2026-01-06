<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class LedgerAccountRecognition extends Model
{
    protected $table = 'ledger_account_recognitions';
    protected $primaryKey = 'code';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'content',
        'recognition',
        'comments'
    ];

    /**
     * Obtener el contenido formateado
     */
    public function getFormattedContent()
    {
        return nl2br($this->content);
    }

    /**
     * Obtener el reconocimiento formateado
     */
    public function getFormattedRecognition()
    {
        return nl2br($this->recognition);
    }

    /**
     * Obtener los comentarios formateados
     */
    public function getFormattedComments()
    {
        return nl2br($this->comments);
    }

    /**
     * Scope para buscar por código
     */
    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Scope para buscar por nombre
     */
    public function scopeByName($query, $name)
    {
        return $query->where('name', 'like', "%{$name}%");
    }
} 
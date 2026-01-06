<?php

namespace App\Models\System;
use Illuminate\Database\Eloquent\Model;

class SubDiaryItem extends Model
{
    protected $table = 'sub_diary_items';
    protected $fillable = [
        'sub_diary_code',
        'code',
        'description',
        'document_number',
        'correlative_number',
        'debit',
        'credit',
        'debit_amount',
        'general_description',
        'credit_amount',
    ];
}
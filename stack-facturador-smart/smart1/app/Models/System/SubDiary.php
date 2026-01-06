<?php

namespace App\Models\System;
use Illuminate\Database\Eloquent\Model;

class SubDiary extends Model
{
    protected $table = 'sub_diaries';
    protected $fillable = [
        'code',
        'date',
        'description',
        'book_code',
    ];


    public function items(){
        return $this->hasMany(SubDiaryItem::class, 'sub_diary_code', 'code');
    }
}
<?php

namespace App\Models\Tenant;

class ItemSeller extends ModelTenant
{

    protected $table = 'items_sellers';
    protected $fillable = [
        'document_item_id',
        'sale_note_item_id',
        'seller_id',
    ];
    public $timestamps = true;

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function document_item()
    {
        return $this->belongsTo(DocumentItem::class)->onDelelete('cascade');
    }
    public function sale_note_item()
    {
        return $this->belongsTo(SaleNoteItem::class)->onDelelete('cascade');
    }
}

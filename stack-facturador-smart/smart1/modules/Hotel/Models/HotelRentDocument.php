<?php

namespace Modules\Hotel\Models;

use App\Models\Tenant\Document;
use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\SaleNote;

class HotelRentDocument extends ModelTenant
{
	protected $table = 'hotel_rent_documents';

	protected $fillable = ['rent_id', 'sale_note_id', 'document_id', 'is_advance'];

	public function rent()
	{
		return $this->belongsTo(HotelRent::class, 'rent_id');
	}

	public function sale_note()
	{
		return $this->belongsTo(SaleNote::class, 'sale_note_id');
	}

	public function document()
	{
		return $this->belongsTo(Document::class, 'document_id');
	}
}

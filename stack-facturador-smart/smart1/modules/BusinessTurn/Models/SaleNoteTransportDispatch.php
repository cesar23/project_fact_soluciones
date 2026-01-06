<?php

namespace Modules\BusinessTurn\Models;
 
use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\Document;
use App\Models\Tenant\Catalogs\IdentityDocumentType;
use App\Models\Tenant\SaleNote;

class SaleNoteTransportDispatch extends ModelTenant
{
    public $timestamps = false;
    protected $table = 'sale_note_transport_dispatches';
    protected $fillable = [
        'sale_note_id',
        's_document_id',
        'sender_number_identity_document',
        'sender_passenger_fullname',
        'sender_telephone',
        'r_document_id',
        'recipient_number_identity_document',
        'recipient_passenger_fullname',
        'recipient_telephone',
        'origin_district_id',
        'origin_address',
        'destinatation_district_id',
        'destinatation_address',
        'agency_origin_id',
        'agency_destination_id',
    ];
    
    public function agency_origin()
    {
        return $this->belongsTo(AgencyTransport::class, 'agency_origin_id');
    }
    public function agency_destination()
    {
        return $this->belongsTo(AgencyTransport::class, 'agency_destination_id');
    }
    public function getOriginDistrictIdAttribute($value)
    {
        return (is_null($value)) ? null : (object) json_decode($value);
    }

    public function setOriginDistrictIdAttribute($value)
    {
        $this->attributes['origin_district_id'] = (is_null($value)) ? null : json_encode($value);
    }

    public function getDestinatationDistrictIdAttribute($value)
    {
        return (is_null($value)) ? null : (object) json_decode($value);
    }

    public function setDestinatationDistrictIdAttribute($value)
    {
        $this->attributes['destinatation_district_id'] = (is_null($value)) ? null : json_encode($value);
    }

    public function sale_note()
    {
        return $this->belongsTo(SaleNote::class);
    }

    public function sender_identity_document_type()
    {
        return $this->belongsTo(IdentityDocumentType::class,  's_document_id');
    }

    public function recipient_identity_document_type()
    {
        return $this->belongsTo(IdentityDocumentType::class,  'r_document_id');
    }

}
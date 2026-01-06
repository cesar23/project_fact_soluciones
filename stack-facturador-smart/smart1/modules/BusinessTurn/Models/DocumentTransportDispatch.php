<?php

namespace Modules\BusinessTurn\Models;
 
use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\Document;
use App\Models\Tenant\Catalogs\IdentityDocumentType;

class DocumentTransportDispatch extends ModelTenant
{   

    public $timestamps = false;
    protected $with = ['sender_identity_document_type', 'recipient_identity_document_type'];
    protected $table = 'document_transport_dispatches';
    protected $fillable = [
        'agency_origin_id',
        'agency_destination_id',
        'document_id',
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
    ];
    public function agency_origin()
    {
        return $this->belongsTo(AgencyTransport::class, 'agency_origin_id');
    }
    public function agency_destination()
    {
        return $this->belongsTo(AgencyTransport::class, 'agency_destination_id');
    }
 

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function sender_identity_document_type()
    {
        return $this->belongsTo(IdentityDocumentType::class, 's_document_id');
    }

    public function recipient_identity_document_type()
    {
        return $this->belongsTo(IdentityDocumentType::class, 'r_document_id');
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

}
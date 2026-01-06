<?php

    namespace Modules\Hotel\Models;

    use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\Person;
use App\Models\Tenant\SaleNote;
use Hyn\Tenancy\Traits\UsesTenantConnection;
    class HotelReservation extends ModelTenant
    {
        use UsesTenantConnection;
        protected $table = 'hotel_reservations';

        protected $fillable = [
            'reservation_date',
            'reservation_method',
            'name',
            'document',
            'sex',
            'age',
            'room_id',
            'number_of_nights',
            'breakfast_type',
            'check_in_date',
            'check_out_date',
            'arrival_time',
            'transfer_in',
            'transfer_out',
            'nightly_rate',
            'total_amount',
            'agency',
            'contact',
            'created_by',
            'observations',
            'customer_id',
            'sale_note_id',
            'active',
            'departure_time',
            'duration_hours',
            'custom_telephone',
        ];
        

        protected $casts = [
            'reservation_date' => 'date',
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'arrival_time' => 'datetime',
            'transfer_in' => 'boolean',
            'transfer_out' => 'boolean',
            'nightly_rate' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'age' => 'integer',
            'number_of_nights' => 'integer',
            'room_id' => 'integer',
            'departure_time' => 'datetime',
            'duration_hours' => 'integer',
        ];

        // Relación con la habitación
        public function room()
        {
            return $this->belongsTo(HotelRoom::class, 'room_id');
        }

        // Relación con la persona
        public function customer()
        {
            return $this->belongsTo(Person::class, 'customer_id');
        }

        // Relación con la nota de venta
        public function sale_note()
        {
            return $this->belongsTo(SaleNote::class, 'sale_note_id');
        }

        


    }

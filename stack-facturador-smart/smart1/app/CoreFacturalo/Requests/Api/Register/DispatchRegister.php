<?php

namespace App\CoreFacturalo\Requests\Api\Register;

use App\Models\Tenant\Person;
use Modules\Dispatch\Models\DispatchAddress;
use Modules\Dispatch\Models\Dispatcher;
use Modules\Dispatch\Models\Driver;
use Modules\Dispatch\Models\Transport;

class DispatchRegister
{
    public static function register($inputs)
    {
        $inputs['driver_id'] = self::driverId($inputs);
        $inputs['transport_id'] = self::transportId($inputs);
        $inputs['dispatcher_id'] = self::dispatcherId($inputs);
        $inputs['sender_id'] = self::senderId($inputs);
        $inputs['receiver_id'] = self::receiverId($inputs);
        $inputs['sender_address_id'] = self::senderAddressId($inputs);
        $inputs['receiver_address_id'] = self::receiverAddressId($inputs);
        return $inputs;
    }

    private static function driverId($inputs)
    {
        if (($inputs['document_type_id'] === '09' && $inputs['transport_mode_type_id'] === '02') || $inputs['document_type_id'] === '31') {
            $driver = $inputs['driver'];
            if (!$driver) {
                return null;
            }
            $record = Driver::query()
                ->firstOrCreate([
                    'identity_document_type_id' => $driver['identity_document_type_id'],
                    'number' => $driver['number']
                ], [
                    'name' => $driver['name'],
                    'license' => $driver['license'],
                    'telephone' => $driver['telephone']
                ]);

            return $record->id;
        }
        return null;
    }

    private static function transportId($inputs)
    {
        if (($inputs['document_type_id'] === '09' && $inputs['transport_mode_type_id'] === '02')  || $inputs['document_type_id'] === '31') {
            $transport = $inputs['transport'];
            if (!$transport) {
                return null;
            }
            $record = Transport::query()
                ->firstOrCreate([
                    'plate_number' => $transport['plate_number']
                ], [
                    'model'                  => $transport['model'],
                    'brand'                  => $transport['brand'],
                    'tuc'                    => $transport['tuc'],
                    'auth_plate_primary'     => $transport['auth_plate_primary'],
                    'secondary_plate_number' => $transport['secondary_plate_number'],
                    'auth_plate_secondary'   => $transport['auth_plate_secondary'],
                    'tuc_secondary'          => $transport['tuc_secondary'],
                ]);

            return $record->id;
        }
        return null;
    }

    private static function dispatcherId($inputs)
    {
        if ($inputs['document_type_id'] === '09' && $inputs['transport_mode_type_id'] === '01') {
            $dispatcher = $inputs['dispatcher'];
            if (!$dispatcher) {
                return null;
            }
            $record = Dispatcher::query()
                ->firstOrCreate([
                    'identity_document_type_id' => $dispatcher['identity_document_type_id'],
                    'number' => $dispatcher['number']
                ], [
                    'name' => $dispatcher['name'],
                    'number_mtc' => $dispatcher['number_mtc'],
                    'address' => '-'
                ]);

            return $record->id;
        }
        return null;
    }

    private static function senderId($inputs)
    {
        if ($inputs['document_type_id'] === '31') {
            $data = $inputs['sender_data'];
            if (!$data) {
                return null;
            }
            $record = Person::query()
                ->firstOrCreate([
                    'country_id' => 'PE',
                    'type' => 'customers',
                    'identity_document_type_id' => $data['identity_document_type_id'],
                    'number' => $data['number']
                ], [
                    'name' => $data['name']
                ]);

            return $record->id;
        }
        return null;
    }

    private static function receiverId($inputs)
    {
        if ($inputs['document_type_id'] === '31') {
            $data = $inputs['receiver_data'];
            if (!$data) {
                return null;
            }
            $record = Person::query()
                ->firstOrCreate([
                    'country_id' => 'PE',
                    'type' => 'customers',
                    'identity_document_type_id' => $data['identity_document_type_id'],
                    'number' => $data['number']
                ], [
                    'name' => $data['name']
                ]);

            return $record->id;
        }
        return null;
    }

    private static function senderAddressId($inputs)
    {
        if ($inputs['document_type_id'] === '31') {
            $data = $inputs['sender_address_data'];
            if (!$data) {
                return null;
            }
            $record = DispatchAddress::query()
                ->firstOrCreate([
                    'person_id' => $inputs['sender_id'],
                    'address' => $data['address']
                ], [
                    'location_id' => $data['location_id'],
                ]);

            return $record->id;
        }
        return null;
    }

    private static function receiverAddressId($inputs)
    {
        if ($inputs['document_type_id'] === '31') {
            $data = $inputs['receiver_address_data'];
            if (!$data) {
                return null;
            }
            $record = DispatchAddress::query()
                ->firstOrCreate([
                    'person_id' => $inputs['receiver_id'],
                    'address' => $data['address']
                ], [
                    'location_id' => $data['location_id'],
                ]);


            return $record->id;
        }
        return null;
    }
}

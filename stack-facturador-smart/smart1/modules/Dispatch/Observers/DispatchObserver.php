<?php

namespace Modules\Dispatch\Observers;

use App\Models\Tenant\Dispatch;

class DispatchObserver
{
    public function saving(Dispatch $record)
    {
        if($record->plate_number) {
            $plate_number = str_replace('-', '', $record->plate_number);
            $plate_number = str_replace(' ', '', $plate_number);
            $record->plate_number = func_str_to_upper_utf8($plate_number);
        }
    }
}

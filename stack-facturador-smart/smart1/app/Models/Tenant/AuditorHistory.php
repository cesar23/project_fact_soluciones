<?php

namespace App\Models\Tenant;

use Hyn\Tenancy\Traits\UsesTenantConnection;

class AuditorHistory extends ModelTenant
{
    use UsesTenantConnection;

    protected $table = 'auditor_history';

    protected $fillable = [
        'user_id',
        'document_id',
        'dispatch_id',
        'new_state_type_id',
        'old_state_type_id',
        'is_edit',
        'is_recreate',
        'is_anulate',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function dispatch()
    {
        return $this->belongsTo(Dispatch::class);
    }

    public function newStateType()
    {
        return $this->belongsTo(StateType::class, 'new_state_type_id');
    }

    public function oldStateType()
    {
        return $this->belongsTo(StateType::class, 'old_state_type_id');
    }
    
    public static function createAnulateDispatch($document){
        $user = auth()->user();
        return self::create([
            'user_id' => $user->id,
            'dispatch_id' => $document->id,
            'new_state_type_id' => '13',
            'old_state_type_id' => $document->state_type_id,
            'is_anulate' => true,
        ]);
    }
    public static function createAnulate($document)
    {
        $user = auth()->user();
        return self::create([
            'user_id' => $user->id,
            'document_id' => $document->id,
            'new_state_type_id' => '13',
            'old_state_type_id' => $document->state_type_id,
            'is_anulate' => true,
        ]);
    }
}

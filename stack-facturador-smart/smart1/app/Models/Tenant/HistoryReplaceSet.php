<?php

    namespace App\Models\Tenant;

    class HistoryReplaceSet extends ModelTenant
    {
        protected $table = 'history_replace_set';
        protected $fillable = [
            'internal_id_item',
            'description_item',
            'item_id',
            'internal_id_replace',
            'description_replace',
            'replace_id',
            'quantity',
            'user_id',
        ];

        public function item()
        {
            return $this->belongsTo(Item::class);
        }

        public function replace_item()
        {
            return $this->belongsTo(Item::class, 'replace_id');
        }

        public function user()
        {
            return $this->belongsTo(User::class);
        }
    }

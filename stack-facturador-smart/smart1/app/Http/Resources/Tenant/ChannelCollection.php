<?php

namespace App\Http\Resources\Tenant;

use App\Models\Tenant\Channel;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ChannelCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->transform(function(Channel $row, $key) {

            return [
                'id' => $row->id,
                'channel_name' => $row->channel_name,
            ];
        });

    }

}

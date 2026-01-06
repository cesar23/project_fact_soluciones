<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\JsonResource;

class TutorialResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        /** @var \App\Models\Tenant\Person $this */
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'description'   => $this->description,
            'link'          => $this->link,
            'image_link'    => ($this->image !== null) ? asset('storage'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'shortcuts'.DIRECTORY_SEPARATOR.$this->image) :null,
            'image'         => $this->image,
            'type'          => (bool) $this->type,
            'location'      => $this->location,
        ];
    }
}

<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TutorialCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function toArray($request)
    {
        return $this->collection->transform(function($row, $key) {
            return [
                'id' => $row->id,
                'title' => $row->title,
                'description' => $row->description,
                'image' => ($row->image !== null) ? asset('storage'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'shortcuts'.DIRECTORY_SEPARATOR.$row->image) : asset("/logo/imagen-no-disponible.jpg"),
                'type' => $row->type ==1 ? "Video Youtube" : "Enlace",
                'location' => $row->location,
                'created_at' => \Carbon\Carbon::parse($row->created_at)->format('d-m-Y')
                //$row->created_at->format('Y-m-d H:i:s'),
             ];
        });
    }
}
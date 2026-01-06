<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductSkusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'price' => (float)$this->price,
            'stock' => $this->stock,
            'attributes' => $this->attributes ? json_decode($this->attributes, true) : [],
            'is_default' => (bool)$this->is_default
        ];
    }
}

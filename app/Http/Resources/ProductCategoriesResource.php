<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductCategoriesResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'icon' => $this->image_url  ,
            'parent_id' => $this->parent_id,
            'sort' => $this->sort,
            // 即使children为空也返回，保持结构一致性
            'children' => ProductCategoriesResource::collection(collect($this->children))
        ];
    }
}

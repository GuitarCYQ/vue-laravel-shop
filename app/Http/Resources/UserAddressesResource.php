<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserAddressesResource extends JsonResource
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
            'user_id' => $this->user_id,
            'province' => $this->province,
            'city' => $this->city,
            'district' => $this->district,
            'address' => $this->address, // 表中实际字段名
            'zip' => $this->zip,
            'contact_name' => $this->contact_name,
            'contact_phone' => $this->contact_phone,
            'full_address' => $this->full_address, // 自动调用访问器
            'last_used_at' => $this->last_used_at,
            'is_default' => $this->is_default,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),

        ];
    }
}

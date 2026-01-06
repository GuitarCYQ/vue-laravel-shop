<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    protected $showSensitiveFields = false;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // 如果showSensitiveFields这个方法为false，就不显示phone、email
        if (!$this->showSensitiveFields) {
            $this->resource->makeHidden(['phone', 'email']);
        }

        $data = parent::toArray($request);
        # 是否绑定手机、微信
        $data['bound_phone'] = $this->resource->phone ? true: false;
        $data['bound_wechat'] = ($this->resource->weixin_unionid || $this->resource->weixin_openid) ? true : false;

        return $data;
    }

    # 开关，控制邮箱和手机号是否隐藏
    public function showSensitiveFields()
    {
        $this->showSensitiveFields = true;

        return $this;
    }
}

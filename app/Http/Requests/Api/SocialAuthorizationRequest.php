<?php

namespace App\Http\Requests\Api;

class SocialAuthorizationRequest extends FormRequest
{
    public function rules()
    {
        // $rules = [
        //     'code' => 'required_without:access_token|string',
        //     'access_token' => 'required_without:code|string',
        //     'state' => 'required|string', // 用于验证的状态参数
        // ];

        // if ($this->social_type == 'wechat' && !$this->code) {
        //     $rules['openid']  = 'required|string';
        // }

        // return $rules;
    }
}

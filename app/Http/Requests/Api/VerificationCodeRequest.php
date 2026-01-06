<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class VerificationCodeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            // 'phone' => 'required|phone:CN,mobile|unique:users',
            // 'email' =>  'required|email',
            'captcha_key' => 'required|string',
            'captcha_code' => 'required|string'
        ];
    }

    public function messages()
    {
        return [
            // 'email.required' => '邮箱不能为空',
            // 'email.email' => '请输入正确的邮箱格式',
            'captcha_key' => '图片验证码 key',
            'captcha_code' => '图片验证码',
        ];
    }
}

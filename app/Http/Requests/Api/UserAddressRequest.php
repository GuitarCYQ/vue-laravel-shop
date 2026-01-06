<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UserAddressRequest extends FormRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            // 收件人姓名：必填，字符串，最大50字符
            'contact_name' => 'required|string|max:50',

            // 联系电话：必填，符合手机号格式（11位数字，以13-9开头）
            'contact_phone' => [
                'required',
                'regex:/^1[3-9]\d{9}$/' // 匹配手机号规则
            ],

            // 省份：必填，字符串（省市区名称通常是中文，无需限制长度）
            'province' => 'required|string',

            // 城市：必填，字符串
            'city' => 'required|string',

            // 区县：必填，字符串
            'district' => 'required|string',

            // 详细地址：必填，字符串，最大255字符（街道、门牌号等）
            'address' => 'required|string|max:255',

            // 邮编：可选，若填写则必须是6位数字
            'zip' => 'nullable|regex:/^\d{6}$/',

            // 新增 is_default 字段规则
            'is_default' => 'boolean' // 允许 true/false/1/0（布尔值验证）

        ];
    }

    public function messages(): array
    {
        return [
            'contact_name.required' => '请填写收件人姓名',
            'contact_name.max' => '收件人姓名不能超过50个字符',

            'contact_phone.required' => '请填写联系电话',
            'contact_phone.regex' => '联系电话格式错误（请输入11位手机号）',

            'province.required' => '请选择省份',
            'city.required' => '请选择城市',
            'district.required' => '请选择区县',

            'address.required' => '请填写详细地址',
            'address.max' => '详细地址不能超过255个字符',

            'zip.regex' => '邮编格式错误（请输入6位数字）',
            // is_default 错误消息
            'is_default.boolean' => '默认地址参数格式错误'
        ];
    }
}
